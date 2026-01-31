<?php

namespace App\Services\ProductService;

use App\DTO\Response\GetPaginatedDTO;
use Illuminate\Http\Request;

interface IProductService
{
    public function CreateProduct(Request $request);
    public  function GetSingleProduct($productId);
    public function UpdateProduct(Request $request, $productId);
    public function GetProductPaginated(GetPaginatedDTO $request);
    public function RequestStocks(Request $request);
    public function GetRequestStocksPaginated(GetPaginatedDTO $request);
    public function ViewSinglePO($poId);
    public function ApproveRejectRequestStock(Request $request, $poId);
    public function ReceiveDelivery($poId);
}
