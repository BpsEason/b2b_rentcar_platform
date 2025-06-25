<?php
namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; # 引入快取門面 (修正語法)
use Illuminate\Support\Facades\DB;    # 用於複雜查詢範例 (修正語法)
use Illuminate\Support\Facades\Http;  # 引入 Http Facade 用於調用 FastAPI (根據優化建議)

class VehicleController extends Controller
{
    /**
     * 獲取所有車輛或根據條件過濾。
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // 範例：使用 Redis 快取查詢結果
        $cacheKey = 'vehicles_' . md5(json_encode($request->all()));
        
        $vehicles = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            $query = Vehicle::with('location');

            if ($request->has('location_id')) {
                $query->where('location_id', $request->location_id);
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            // TODO: 添加多租戶過濾：$query->where('tenant_id', auth()->user()->tenant_id);

            return $query->get();
        });

        return response()->json($vehicles);
    }

    /**
     * 獲取單一車輛詳情。
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $vehicle = Vehicle::with('location')->findOrFail($id);
        // TODO: 添加多租戶檢查：if ($vehicle->tenant_id !== auth()->user()->tenant_id) abort(403);
        return response()->json($vehicle);
    }

    /**
     * 新增車輛 (需要管理員權限，或特定角色)。
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => 'required|string|unique:vehicles,license_plate',
            'year' => 'required|string|max:4',
            'daily_rate' => 'required|numeric|min:0',
            // TODO: 添加更多驗證規則
        ]);

        $vehicle = Vehicle::create($request->all());
        // TODO: 設定 tenant_id

        Cache::forget('vehicles_*'); // 新增後清除相關快取
        return response()->json($vehicle, 201);
    }

    /**
     * 更新車輛 (需要管理員權限)。
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        // TODO: 添加多租戶檢查
        
        $request->validate([
            'location_id' => 'sometimes|exists:locations,id',
            'license_plate' => 'sometimes|string|unique:vehicles,license_plate,' . $id,
            // TODO: 添加更多驗證規則
        ]);

        $vehicle->update($request->all());

        Cache::forget('vehicles_*'); // 更新後清除相關快取
        return response()->json($vehicle);
    }

    /**
     * 刪除車輛 (需要管理員權限)。
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        // TODO: 添加多租戶檢查
        $vehicle->delete();

        Cache::forget('vehicles_*'); // 刪除後清除相關快取
        return response()->json(null, 204);
    }

    /**
     * 搜尋可用車輛 (複雜業務邏輯)。
     * 可能需要與 FastAPI 的動態定價或推薦服務結合。
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $request->validate([
            'pickup_location_id' => 'required|exists:locations,id',
            'return_location_id' => 'required|exists:locations,id',
            'pickup_datetime' => 'required|date',
            'return_datetime' => 'required|date|after:pickup_datetime',
        ]);

        $pickupLocationId = $request->pickup_location_id;
        $returnLocationId = $request->return_location_id;
        $pickupDateTime = $request->pickup_datetime;
        $returnDateTime = $request->return_datetime;

        // 範例：查詢在指定時間範圍和地點可用的車輛
        $availableVehicles = Vehicle::where('status', 'available')
            ->where('location_id', $pickupLocationId)
            ->whereDoesntHave('bookings', function ($query) use ($pickupDateTime, $returnDateTime) {
                $query->where(function ($q) use ($pickupDateTime, $returnDateTime) {
                    $q->where('pickup_datetime', '<', $returnDateTime)
                      ->where('return_datetime', '>', $pickupDateTime);
                })->whereIn('status', ['confirmed', 'rented']); // 只考慮已確認或正在租賃的訂單
            })
            // TODO: 添加多租戶過濾
            ->get();
        
        // 這裡考慮呼叫 FastAPI 服務來獲取推薦或更精確的動態價格 (根據優化建議)
        $fastApiUrl = env('VITE_APP_FASTAPI_API_URL');
        if ($fastApiUrl) {
            try {
                $pricingResponse = Http::post("{$fastApiUrl}/pricing/calculate", [
                    'vehicle_ids' => $availableVehicles->pluck('id')->toArray(), # 將所有可用車輛ID傳遞給FastAPI
                    'pickup_datetime' => $pickupDateTime,
                    'return_datetime' => $returnDateTime,
                    'pickup_location_id' => $pickupLocationId,
                ]);

                if ($pricingResponse->successful()) {
                    $dynamicPrices = collect($pricingResponse->json())->keyBy('vehicle_id');
                    $availableVehicles->each(function ($vehicle) use ($dynamicPrices) {
                        $vehicle->dynamic_price = $dynamicPrices->get($vehicle->id)['final_price'] ?? $vehicle->daily_rate;
                    });
                } else {
                    \Log::warning('FastAPI 定價服務返回錯誤或失敗: ' . $pricingResponse->body());
                }
            } catch (\Exception $e) {
                \Log::error('呼叫 FastAPI 定價服務失敗: ' . $e->getMessage());
            }
        }

        return response()->json($availableVehicles);
    }
}
