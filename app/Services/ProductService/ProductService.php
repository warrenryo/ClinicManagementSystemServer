<?php

namespace App\Services\ProductService;

use App\DTO\Response\GetPaginatedDTO;
use App\DTO\Response\PaginatedTableResponse;
use App\Enums\ApprovalStatus;
use App\Enums\UOM;
use App\Helpers\UserHelper;
use App\Models\Inventory\Products;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderItems;
use App\Response\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isArray;

class ProductService implements IProductService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function CreateProduct(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Title' => 'required|string',
                'Description' => 'nullable|string',
                'UOM' => 'required|integer',
                'ReflenishAmount' => 'required|numeric',
            ]);

            Products::create([
                'title' => $validatedData['Title'],
                'description' => $validatedData['Description'],
                'uom' => UOM::from($validatedData['UOM'])->value,
                'reflenish_amount' => $validatedData['ReflenishAmount'],
                'at_cost' => 0
            ]);

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public  function GetSingleProduct($productId)
    {
        try {
            $product = Products::findOrFail($productId);

            $result = [
                'Id' => $product->id,
                'Title' => $product->title,
                'Description' => $product->description,
                'UOM' => $product->uom,
                'AtCost' => $product->at_cost,
                'ReflenishAmount' => $product->reflenish_amount,
            ];

            return ResponseHelper::successWData(200, "Success", $result);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function UpdateProduct(Request $request, $productId)
    {
        try {
            $validatedData = $request->validate([
                'Title' => 'required|string',
                'Description' => 'nullable|string',
                'UOM' => 'required|integer',
                'ReflenishAmount' => 'required|numeric',
            ]);

            $product = Products::findOrFail($productId);

            $product->update([
                'title' => $validatedData['Title'],
                'description' => $validatedData['Description'],
                'uom' => $validatedData['UOM'],
                'reflenish_amount' => $validatedData['ReflenishAmount'],
                'at_cost' => 0
            ]);

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetProductPaginated(GetPaginatedDTO $request)
    {
        try {
            $query = Products::query()
                ->search(
                    $request->SearchValue,
                    ['title', 'description'],
                    [],
                    [],
                    []
                );

            $count = $query->count();

            $products = $query
                ->orderBy('id', 'desc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $paginated = $products->map(function ($prod) {
                return [
                    'Id' => $prod->id,
                    'Title' => $prod->title,
                    'UOM' => $prod->uom,
                    'Quantity' => $prod->quantity,
                    'PackagingQty' => (int) $prod->reflenish_amount
                ];
            })->toArray();

            $result = new PaginatedTableResponse($paginated, $count);

            return ResponseHelper::successWData(200, "Success", $result);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function RequestStocks(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Notes' => 'nullable|string',
                'SelectedProducts' => 'required|array',
                'SelectedProducts.*.ProductId' => 'required|exists:products,id',
                'SelectedProducts.*.Quantity' => 'required|integer|min:1',
            ]);

            DB::transaction(function () use ($validatedData) {
                $user_details_id = UserHelper::getUserDetailsId();

                $purchase_order = PurchaseOrder::create([
                    'created_by_id' => $user_details_id,
                    'notes' => $validatedData['Notes'] ?? null,
                    'approval_status' => ApprovalStatus::PENDING->value,
                ]);

                if (is_array($validatedData['SelectedProducts'])) {
                    foreach ($validatedData['SelectedProducts'] as $product) {
                        PurchaseOrderItems::create([
                            'purchase_order_id' => $purchase_order->id,
                            'product_id' => $product['ProductId'],
                            'quantity' => $product['Quantity'],
                            'at_cost' => 0
                        ]);
                    }
                }
            });

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetRequestStocksPaginated(GetPaginatedDTO $request)
    {
        try {
            $query = PurchaseOrder::query();

            if (!empty($request->ApprovalStatus)) {
                $query->where(
                    'approval_status',
                    ApprovalStatus::from($request->ApprovalStatus)->value
                );
            }

            $count = $query->count();

            $purchase_order = $query
                ->orderBy('id', 'desc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $paginated = $purchase_order->map(function ($po) {
                return [
                    'Id' => $po->id,
                    'CreatedBy' => $po->userDetails->first_name . ' ' . $po->userDetails->last_name,
                    'DateCreated' => $po->created_at,
                    'TotalItems' => $po->purchaseOrderItems->count(),
                    'ApprovalStatus' => ApprovalStatus::from($po->approval_status)->value,
                ];
            })->toArray();

            $result = new PaginatedTableResponse($paginated, $count);
            return ResponseHelper::successWData(200, "Success", $result);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function ViewSinglePO($poId)
    {
        try {
            $po = PurchaseOrder::findOrFail($poId);

            $data = [
                'Notes' => $po->notes,
                'ApprovalStatus' => $po->approval_status,
                'RejectReason' => $po->reject_reason,
                'SelectedProducts' => $po->purchaseOrderItems->map(function ($item) {
                    return [
                        'ProductId' => $item->id,
                        'Title' => $item->products->title,
                        'UOM' => UOM::from($item->products->uom)->value,
                        'PkgQty' => (int)$item->products->reflenish_amount,
                        'Quantity' => $item->quantity,
                        'Receive' => $item->quantity * $item->products->reflenish_amount,
                        'AtCostPrice' => $item->at_cost ?? null,
                    ];
                })
            ];

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function ApproveRejectRequestStock(Request $request, $poId)
    {
        try {
            $validatedData = $request->validate([
                'Notes'          => 'nullable|string',
                'ApprovalStatus' => 'required|integer',
                'RejectReason'   => 'nullable|string',

                'SelectedProducts' => 'nullable|array',
            ]);

            DB::transaction(function () use ($validatedData, $poId) {

                $purchaseOrder = PurchaseOrder::findOrFail($poId);

                $purchaseOrder->update([
                    'reject_reason'   => $validatedData['RejectReason'] ?? null,
                    'approval_status' => ApprovalStatus::from(
                        $validatedData['ApprovalStatus']
                    )->value,
                ]);

                // Only update stock & costs when APPROVED
                if (
                    !empty($validatedData['SelectedProducts']) &&
                    $validatedData['ApprovalStatus'] == ApprovalStatus::APPROVED->value
                ) {
                    foreach ($validatedData['SelectedProducts'] as $product) {

                        $poItem = PurchaseOrderItems::where('purchase_order_id', $poId)
                            ->where('id', $product['ProductId'])
                            ->first();

                        if (!$poItem) {
                            continue;
                        }

                        $poItem->update([
                            'at_cost' => $product['AtCostPrice'],
                        ]);

                        // $masterProduct = Products::find($poItem->product_id);

                        // if ($masterProduct) {
                        //     $masterProduct->quantity +=
                        //         ($product['Quantity'] * $masterProduct->reflenish_amount);

                        //     $masterProduct->save();
                        // }
                    }
                }
            });

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function ReceiveDelivery($poId)
    {
        try {

            $purchaseOrder = PurchaseOrder::findOrFail($poId);


            foreach ($purchaseOrder->purchaseOrderItems as $product) {
                $masterProduct = Products::find($product->product_id);

                if ($masterProduct) {
                    $masterProduct->quantity +=
                        ($product->quantity * $masterProduct->reflenish_amount);

                    $masterProduct->save();
                }
            }

            $purchaseOrder->approval_status = ApprovalStatus::RECEIVED->value;
            $purchaseOrder->save();

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }
}
