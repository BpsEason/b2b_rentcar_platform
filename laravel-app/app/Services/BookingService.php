<?php
namespace App\Services;

use App\Models\Vehicle;
use App\Models\Booking;
use Carbon\Carbon;

class BookingService
{
    /**
     * 檢查車輛在指定時間段內是否可用。
     * @param int $vehicleId
     * @param string $pickupDateTime
     * @param string $returnDateTime
     * @return bool
     */
    public function isVehicleAvailable(int $vehicleId, string $pickupDateTime, string $returnDateTime): bool
    {
        // 確保日期時間格式正確
        $pickup = Carbon::parse($pickupDateTime);
        $return = Carbon::parse($returnDateTime);

        if ($pickup->greaterThanOrEqualTo($return)) {
            return false; // 取車時間不能晚於或等於還車時間
        }

        // 檢查車輛是否存在且狀態為可用
        $vehicle = Vehicle::where('id', $vehicleId)
                          ->where('status', 'available')
                          // TODO: 添加多租戶過濾：->where('tenant_id', auth()->user()->tenant_id)
                          ->first();
        
        if (!$vehicle) {
            return false;
        }

        // 檢查是否有重疊的已確認或正在租賃的訂單
        $overlappingBookings = Booking::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['confirmed', 'rented']) // 只考慮已確認和正在租賃的訂單
            ->where(function ($query) use ($pickup, $return) {
                $query->where('pickup_datetime', '<', $return)
                      ->where('return_datetime', '>', $pickup);
            })
            ->count();

        return $overlappingBookings === 0;
    }

    // TODO: 添加計算租賃費用、處理付款、更新車輛狀態等方法
}
