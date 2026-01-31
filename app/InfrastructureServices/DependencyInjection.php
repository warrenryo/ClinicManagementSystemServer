<?php

namespace App\InfrastructureServices;

use App\Services\AppointmentService\AppointmentService;
use App\Services\AppointmentService\IAppointmentService;
use App\Services\AuthService\AuthService;
use Illuminate\Contracts\Foundation\Application;
use App\Services\AuthService\IAuthService;
use App\Services\AzureBlobStorageService\AzureBlobStorageService;
use App\Services\AzureBlobStorageService\IAzureBlobStoragInterface;
use App\Services\DashboardService\DashboardService;
use App\Services\DashboardService\IDashboardService;
use App\Services\DoctorAppointmentService\DoctorAppointmentService;
use App\Services\DoctorAppointmentService\IDoctorAppointmentService;
use App\Services\GeminiService\GeminiService;
use App\Services\GeminiService\IGeminiService;
use App\Services\LocalUploadService\ILocalUploadService;
use App\Services\LocalUploadService\LocalUploadService;
use App\Services\MedicalRecordService\IMedicalRecordService;
use App\Services\MedicalRecordService\MedicalRecordService;
use App\Services\ProcurementService\IProcurementService;
use App\Services\ProcurementService\ProcurementService;
use App\Services\ProductService\IProductService;
use App\Services\ProductService\ProductService;
use App\Services\UserService\IUserService;
use App\Services\UserService\UserService;

class DependencyInjection
{
    public static function AppServices(Application $app)
    {
        $app->bind(IAuthService::class, AuthService::class);
        $app->bind(IUserService::class, UserService::class);
        $app->bind(IAzureBlobStoragInterface::class, AzureBlobStorageService::class);
        $app->bind(ILocalUploadService::class, LocalUploadService::class);
        $app->bind(IAppointmentService::class, AppointmentService::class);
        $app->bind(IDoctorAppointmentService::class, DoctorAppointmentService::class);
        $app->bind(IMedicalRecordService::class, MedicalRecordService::class);
        $app->bind(IProductService::class, ProductService::class);
        $app->bind(IDashboardService::class, DashboardService::class);
        $app->bind(IGeminiService::class, GeminiService::class);
    }
}
