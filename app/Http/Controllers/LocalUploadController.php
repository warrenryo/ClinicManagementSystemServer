<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interface\LocalUploadInterface;
use App\Response\ResponseHelper;

class LocalUploadController extends Controller
{
    protected $localUploadService;

    public function __construct(LocalUploadInterface $localUploadService)
    {
        $this->localUploadService = $localUploadService;
    }

    public function UploadImageLocal(Request $request)
    {
        try {
            $response = $this->localUploadService->UploadImage($request);
            return ResponseHelper::getStatusResponse($response);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function UploadMultipleImageLocal(Request $request)
    {
        try {
            $response = $this->localUploadService->UploadMultipleImages($request);
            return ResponseHelper::getStatusResponse($response);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function UploadMultipleFiles(Request $request)
    {
        try {
            $response = $this->localUploadService->UploadMultipleFiles($request);
            return ResponseHelper::getStatusResponse($response);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 400);
        }
    }
}
