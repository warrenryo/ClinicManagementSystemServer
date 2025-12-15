<?php

namespace App\Services\AzureBlobStorageService;

use Illuminate\Http\Request;

interface IAzureBlobStoragInterface
{
    public function UploadImage(Request $request);
}
