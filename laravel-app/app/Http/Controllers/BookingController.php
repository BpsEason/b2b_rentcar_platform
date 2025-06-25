<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BookingService;
use Junges\Kafka\Facades\Kafka; # 引入 Kafka (修正語法)
use Junges\Kafka\Message\Message;
use Illuminate\Support\Facades\Log; # 引入 Log (修正語法)
use Illuminate\Support\Facades\Cache; # 引入 Cache (修正語法)


class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
        $this->middleware('auth:api'); // 確保所有方法都需要 JWT 認證
    }

    /**
     * 獲取當前用戶的所有訂單。
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // 獲取當前認證用戶的訂單
        $bookings = auth()->user()->bookings()->with(['vehicle', 'pickupLocation', 'returnLocation'])->get();
        return response()->json($bookings);
    }

    /**
     * 獲取當前用戶的訂單 (前端特定路由)。
     * @return \Illuminate\Http\JsonResponse
     */
    public function userBookings()
    {
        return $this->index();
    }

    /**
     * 獲取單一訂單詳情。
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $booking = Booking::with(['vehicle', 'pickupLocation', 'returnLocation'])->findOrFail($id);

        // 確保用戶只能查看自己的訂單
        if ($booking->user_id !== auth()->id()) {
            abort(403, '未經授權');
        }
        // TODO: 添加多租戶檢查

        return response()->json($booking);
    }

    /**
     * 創建新訂單。
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'pickup_location_id' => 'required|exists:locations,id',
            'return_location_id' => 'required|exists:locations,id',
            'pickup_datetime' => 'required|date|after_or_equal:now',
            'return_datetime' => 'required|date|after:pickup_datetime',
            'total_amount' => 'required|numeric|min:0',
            // TODO: 添加付款資訊的驗證，這裡僅為範例
        ]);

        $userId = auth()->id();
        $vehicleId = $request->vehicle_id;
        $pickupDateTime = $request->pickup_datetime;
        $returnDateTime = $request->return_datetime;

        // 檢查車輛是否可用 (再次檢查，防止並發問題)
        if (!$this->bookingService->isVehicleAvailable($vehicleId, $pickupDateTime, $returnDateTime)) {
            return response()->json(['message' => '此時段車輛不可用或已被預訂'], 409);
        }

        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'user_id' => $userId,
                'vehicle_id' => $vehicleId,
                'pickup_location_id' => $request->pickup_location_id,
                'return_location_id' => $request->return_location_id,
                'pickup_datetime' => $pickupDateTime,
                'return_datetime' => $returnDateTime,
                'total_amount' => $request->total_amount,
                'status' => 'pending', // 初始狀態為待確認
                'payment_status' => 'pending',
                // TODO: 設定 tenant_id
            ]);

            // 範例：發送 Kafka 事件 (例如通知付款服務、車輛狀態更新服務)
            // 需要在 config/kafka.php 配置主題和 broker
            try {
                Kafka::publish(
                    new Message(
                        headers: ['type' => 'booking_created'],
                        body: [
                            'booking_id' => $booking->id,
                            'user_id' => $booking->user_id,
                            'vehicle_id' => $booking->vehicle_id,
                            'total_amount' => $booking->total_amount,
                            'pickup_datetime' => $booking->pickup_datetime->toIso8601String(),
                            'return_datetime' => $booking->return_datetime->toIso8601String(),
                            'status' => $booking->status,
                        ],
                        key: (string)$booking->id
                    )
                )->onTopic('booking_events')->send();
            } catch (\Exception $e) {
                Log::error('無法發送 Kafka 訊息: ' . $e->getMessage());
                // 這裡可以考慮將發送失敗的訊息記錄到死信隊列或再次嘗試
            }

            // 範例：推送到 RabbitMQ 隊列處理後續付款或通知
            // dispatch(new ProcessPayment($booking)); // 需要定義 ProcessPayment Job

            DB::commit();

            return response()->json($booking, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('創建訂單失敗: ' . $e->getMessage());
            return response()->json(['message' => '創建訂單時發生錯誤', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 取消訂單。
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $id) # 添加取消訂單功能 (根據優化建議)
    {
        $booking = Booking::findOrFail($id);
        if ($booking->user_id !== auth()->id()) {
            abort(403, '未經授權');
        }
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => '無法取消已租賃或已完成的訂單'], 400);
        }
        $booking->update(['status' => 'cancelled']);
        Cache::forget('bookings_*'); # 清除相關快取

        try {
            Kafka::publish(new Message(
                headers: ['type' => 'booking_cancelled'],
                body: [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'timestamp' => now()->toIso8601String(),
                ],
                key: (string)$booking->id
            ))->onTopic('booking_events')->send();
        } catch (\Exception $e) {
            Log::error('Kafka 訊息發送失敗: ' . $e->getMessage());
        }
        return response()->json(['message' => '訂單已取消'], 200);
    }
    // TODO: 添加 update 方法
}
