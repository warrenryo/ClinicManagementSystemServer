<?php

namespace App\Services\AzureBlobStorageService;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AzureBlobStorageService implements IAzureBlobStoragInterface
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
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('image');
        $filename = 'ryonixlaravel-' . (string) Str::uuid() . '.' . $file->getClientOriginalExtension();

        $path = Storage::disk('azure')->putFileAs('ryonixlaravel', $file, $filename);
        // You can also use ->put() or ->writeStream()

        $url = Storage::disk('azure')->url($path);

        return $url;
    }
}
