<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http; # 新增 Http Facade
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 公開路由
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// 需要認證的路由 (添加速率限制，根據優化建議)
Route.middleware(['auth:api', 'throttle:60,1'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/me', [AuthController::class, 'me']);

    // 車輛相關
    Route::get('vehicles', [VehicleController::class, 'index']);
    Route::get('vehicles/{id}', [VehicleController::class, 'show']);
    Route::post('vehicles', [VehicleController::class, 'store']); # 範例：管理員新增
    Route::put('vehicles/{id}', [VehicleController::class, 'update']); # 範例：管理員更新
    Route::delete('vehicles/{id}', [VehicleController::class, 'destroy']); # 範例：管理員刪除
    Route::get('vehicles/search', [VehicleController::class, 'search']); # 車輛搜尋

    // 據點相關
    Route::get('locations', [LocationController::class, 'index']);
    Route::get('locations/{id}', [LocationController::class, 'show']);

    // 預訂相關
    Route::get('bookings', [BookingController::class, 'index']); # 用戶自己的訂單
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel']); # 添加取消訂單路由

    // 用戶自己的訂單 (通常透過 Auth 獲取)
    Route::get('user/bookings', [BookingController::class, 'userBookings']);
});
