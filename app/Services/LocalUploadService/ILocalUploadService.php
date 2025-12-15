<?php

namespace App\Services\LocalUploadService;

use Illuminate\Http\Request;

interface ILocalUploadService
{
    public function UploadImage(Request $request);
    public function UploadMultipleImages(Request $request);
    public function UploadMultipleFiles(Request $request);
}
