<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AzureBlobStorageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorAppointmentController;
use App\Http\Controllers\LocalUploadController;
use App\Http\Controllers\MedicalRecordsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'auth'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login',  'Login');
        Route::post('register',  'Register');
        Route::post('refresh-token', 'RefreshToken');
        Route::post('logout', 'Logout');
    });

    Route::controller(UserController::class)->group(function () {
        Route::put('update-profile', 'UpdateProfile');
        Route::get('get-user-details', 'GetUserDetails');
    });
});



Route::middleware(['authenticate.user.token'])->group(function () {
    Route::group(['prefix' => 'azureblob'], function () {
        Route::controller(AzureBlobStorageController::class)->group(function () {
            Route::post('upload-image', 'UploadImage');
        });
    });

    Route::group(['prefix' => 'localupload'], function () {
        Route::controller(LocalUploadController::class)->group(function () {
            Route::post('upload-image', 'UploadImageLocal');
            Route::post('upload-multiple-image', 'UploadMultipleImageLocal');
            Route::post('upload-multiple-files', 'UploadMultipleFiles');
        });
    });



    Route::group(['prefix' => 'users'], function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('get-all-users-paginated', 'GetAllUserPaginated');
            Route::put('toggle-user-status/{id}', 'ToggleUserStatus');
            Route::get('get-all-users-list', 'GetAllUsersList');
            Route::get('get-all-doctors-paginated', 'GetAllDoctorsPaginated');
        });
    });

    Route::group(['prefix' => 'appointment'], function () {
        Route::controller(AppointmentController::class)->group(function () {
            Route::post('create-appointment', 'CreateAppointment');
            Route::get('get-appointment-dates', 'GetAppointmentDates');
            Route::get('get-user-appointments', 'GetUserAppointments');
            Route::get('get-user-appointment-details/{appointmentId}', 'GetUserAppointmentDetails');
            Route::get('get-overall-appointments', 'GetOverallAppointments');
            Route::get('get-appointments-by-date/{date}', 'GetAppointmentsByDate');
            Route::get('get-appointment-calendar-counts/{month}/{year}', 'GetAppointmentCalendarCounts');
            Route::put('set-appointment-status/{appointmentId}/{status}', 'SetAppointmentStatus');
            Route::put('reschedule-appointment/{appointmentId}', 'RescheduleAppointment');
            Route::put('assign-doctor-to-appointment', 'AssignDoctorToAppointment');
        });
    });

    Route::group(['prefix' => 'doctorappointment'], function () {
        Route::controller(DoctorAppointmentController::class)->group(function () {
            Route::get('get-doctor-appointment-paginated', 'GetDoctorAppointmentPaginated');
            Route::put('reassign-doctor', 'ReassignDoctor');
            Route::post('add-vital-sign/{appointmentId}', 'AddVitalSign');
        });
    });

    Route::group(['prefix' => 'medicalrecords'], function () {
        Route::controller(MedicalRecordsController::class)->group(function () {
            Route::post('add-vital-sign/{appointmentId}', 'AddVitalSign');
            Route::get('get-initial-records/{appointmentId}', 'GetAppointmentMedical');
            Route::post('create-medical-records', 'CreateMedicalRecord');
        });
    });

    Route::group(['prefix' => 'products'], function () {
        Route::controller(ProductController::class)->group(function () {
            Route::post('create-product', 'CreateProduct');
            Route::get('get-single-product/{productId}', 'GetSingleProduct');
            Route::put('update-product/{productId}', 'UpdateProduct');
            Route::get('get-product-paginated', 'GetProductPaginated');
            Route::post('request-stocks', 'RequestStocks');
            Route::get('get-request-stocks-paginated', 'GetRequestStocksPaginated');
            Route::get('get-single-po/{poId}', 'ViewSinglePO');
            Route::put('update-stock-request/{poId}', 'ApproveRejectRequestStock');
            Route::put('receive-delivery/{poId}', 'ReceiveDelivery');
        });
    });

    Route::group(['prefix' => 'dashboard'], function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('get-dashboard-card', 'DashCards');
            Route::get('get-appointment-analytics', 'GetAppointmentAnalytics');
            Route::get('get-inventory-cost-analytics', 'GetPurchaseOrderAtCostChart');
            Route::get('get-appointment-analytics', 'GetAppointmentCharData');
            Route::post('generate-summary', 'generateClinicSummary');
            Route::get('get-latest-summary', 'GetLatestAISummary');
            Route::get('get-appointment-reason-distribution', 'GetAppointmentReasonDistribution');
            Route::get('get-appointment-reason-trend', 'GetAppointmentReasonTrend');
        });
    });
});
