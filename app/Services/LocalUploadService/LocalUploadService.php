<?php

namespace App\Services\LocalUploadService;

use Illuminate\Http\Request;
use App\Response\ResponseHelper;
use App\Services\LocalUploadService\ILocalUploadService;

class LocalUploadService implements ILocalUploadService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function UploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploaded_imgs'), $imageName);

            $app_url = config('app.url');
            $image_url = "{$app_url}/uploaded_imgs/{$imageName}";


            return ResponseHelper::successWData(200, 'Image has been uploaded', $image_url);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "Something went wrong {$th->getMessage()}");
        }
    }

    public function UploadMultipleImages(Request $request)
    {
        try {
            $request->validate([
                'images' => 'required|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);

            $uploadeUrls = [];
            $app_url = config('app.url');

            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploaded_imgs'), $imageName);

                $uploadeUrls[] = "{$app_url}/uploaded_imgs/{$imageName}";
            }

            return ResponseHelper::successWData(200, "Image have been uploaded", $uploadeUrls);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "Something went wrong {$th->getMessage()}");
        }
    }

    public function UploadMultipleFiles(Request $request)
    {
        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'file|max:10240' // 10mb
            ]);

            $uploadeUrls = [];
            $app_url = config('app.url');

            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $safeName = preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $originalName);

                $mime_type = $file->getClientMimeType();
                $file_size = $file->getSize();
                $file->move(public_path('uploaded_files'), $safeName);
                $file_url = "{$app_url}/uploaded_files/{$safeName}";

                $uploadeUrls[] = [
                    'file_url' => $file_url,
                    'file_type' => $mime_type,
                    'file_size' => $file_size
                ];
            }

            return ResponseHelper::successWData(200, "Files have been uploaded", $uploadeUrls);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "Something went wrong {$th->getMessage()}");
        }
    }
}
