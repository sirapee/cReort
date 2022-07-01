<?php

use App\Http\Controllers\API\SessionController;
use App\Http\Controllers\API\SettlementController;
use App\Http\Controllers\API\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1/'], function () {
    /*Route::apiResource('todo-list', TodoListController::class);

    Route::apiResource('todo-list.task', TaskController::class)
        ->except('show')
        ->shallow();

    Route::apiResource('label', LabelController::class);*/

    Route::post('/upload-settlement', [SettlementController::class, 'uploadAndExtractSettlementFiles'])->name('settlement.upload');

    Route::group(array('prefix' => 'session'), function () {
        Route::post('/user-login', [SessionController::class, 'userLogin'] );
        Route::post('/two-factor', [SessionController::class, 'twoFactor'] );
        Route::get('/refresh-token', [SessionController::class, 'refreshToken'] );
        Route::get('/authenticated-user', [SessionController::class, 'getAuthenticatedUser'] );
        Route::get('/user-logout', [SessionController::class, 'logout'] );

    });

    Route::group(array('prefix' => 'users'), function () {
        Route::get('/', [UserManagementController::class, 'index'] );
        Route::get('/getAllRoutes', [UserManagementController::class, 'getAllRoutes'] );
        Route::get('/roles', [UserManagementController::class, 'roles'] );
        Route::get('/getPermissions/{slug}', [UserManagementController::class, 'permissionsDetails'] );

        Route::post('/', [UserManagementController::class, 'store'] );
        Route::get('/{userId}/edit', [UserManagementController::class, 'edit'] );
        Route::patch('{userId}/edit', [UserManagementController::class, 'update'] );
        Route::get('/{userId}/show', [UserManagementController::class, 'show'] );
        Route::get('/getUserADDetails/{id}', [UserManagementController::class, 'details'] );

        Route::get('{id}/view', [UserManagementController::class, 'viewDetails'] );
        Route::post('/verify', [UserManagementController::class, 'verify'] );
        Route::get('/addExtras', [UserManagementController::class, 'addExtras'] );
        Route::get('/pending', [UserManagementController::class, 'pendingAuthorization'] );
        Route::get('/export', [UserManagementController::class, 'usersDetailsDownload'] );


        Route::delete('/delete', [UserManagementController::class, 'destroy'] );
        Route::get('/restore', [UserManagementController::class, 'restore'] );


    });
});


