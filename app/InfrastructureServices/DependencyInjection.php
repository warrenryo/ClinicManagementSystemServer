<?php

namespace App\InfrastructureServices;

use App\Services\AuthService\AuthService;
use Illuminate\Contracts\Foundation\Application;
use App\Services\AuthService\IAuthService;
use App\Services\AzureBlobStorageService\AzureBlobStorageService;
use App\Services\AzureBlobStorageService\IAzureBlobStoragInterface;
use App\Services\LocalUploadService\ILocalUploadService;
use App\Services\LocalUploadService\LocalUploadService;
use App\Services\UserService;
use App\Services\UserService\IUserService;

class DependencyInjection
{
    public static function AppServices(Application $app)
    {
        $app->bind(IAuthService::class, AuthService::class);
        $app->bind(IUserService::class, UserService::class);
        $app->bind(IAzureBlobStoragInterface::class, AzureBlobStorageService::class);
        $app->bind(ILocalUploadService::class, LocalUploadService::class);
    }
}
