<?php

namespace App\Http\Controllers;

use App\Services\AzureBlobStorageService;
use Illuminate\Http\Request;

class AzureBlobStorageController extends Controller
{
    protected $blobStorage;

    public function __construct(AzureBlobStorageService $blobStorage)
    {
        $this->blobStorage = $blobStorage;
    }

    public function UploadImage(Request $request)
    {
        try {
            $status = $this->blobStorage->UploadImage($request);

            return response()->json($status);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
