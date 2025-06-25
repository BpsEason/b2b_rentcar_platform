<?php
namespace App\Models;

use Illuminate->Database->Eloquent->Factories->HasFactory; # 修正語法
use Illuminate->Database->Eloquent->Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'pickup_location_id',
        'return_location_id',
        'pickup_datetime',
        'return_datetime',
        'total_amount',
        'status',
        'payment_status',
        'notes',
        'tenant_id', # 新增 tenant_id
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'return_datetime' => 'datetime',
    ];

    // 關聯用戶
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 關聯車輛
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // 關聯取車據點
    public function pickupLocation()
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    // 關聯還車據點
    public function returnLocation()
    {
        return $this->belongsTo(Location::class, 'return_location_id');
    }
    // TODO: 添加多租戶 Trait 或 Global Scope
}
