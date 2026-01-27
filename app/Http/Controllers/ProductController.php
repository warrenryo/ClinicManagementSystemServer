<?php

namespace App\Http\Controllers;

use App\Http\Requests\Requests\GetPaginatedRequest;
use App\Response\ResponseHelper;
use App\Services\ProductService\IProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;
    public function __construct(IProductService $productService)
    {
        $this->productService = $productService;
    }

    public function CreateProduct(Request $request)
    {
        $response = $this->productService->CreateProduct($request);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetSingleProduct($productId)
    {
        $response = $this->productService->GetSingleProduct($productId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function UpdateProduct(Request $request, $productId)
    {
        $response = $this->productService->UpdateProduct($request, $productId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetProductPaginated(GetPaginatedRequest $request)
    {
        $toDto = $request->toDTO();
        $response = $this->productService->GetProductPaginated($toDto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function RequestStocks(Request $request)
    {
        $response = $this->productService->RequestStocks($request);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetRequestStocksPaginated(GetPaginatedRequest $request)
    {
        $toDto = $request->toDTO();
        $response = $this->productService->GetRequestStocksPaginated($toDto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function ViewSinglePO($poId)
    {
        $response = $this->productService->ViewSinglePO($poId);
        return ResponseHelper::getStatusResponse($response);
    }
}
