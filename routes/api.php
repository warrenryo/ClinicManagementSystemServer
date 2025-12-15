<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AzureBlobStorageController;
use App\Http\Controllers\LocalUploadController;
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
        });
    });
});
