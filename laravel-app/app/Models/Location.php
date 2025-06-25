<?php
namespace App\Models;

use Illuminate->Database->Eloquent->Factories->HasFactory; # 修正語法
use Illuminate->Database->Eloquent->Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'phone',
        'latitude',
        'longitude',
        'tenant_id', # 新增 tenant_id
    ];

    // 關聯車輛
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    // 關聯作為取車據點的訂單
    public function pickupBookings()
    {
        return $this->hasMany(Booking::class, 'pickup_location_id');
    }

    // 關聯作為還車據點的訂單
    public function returnBookings()
    {
        return $this->hasMany(Booking::class, 'return_location_id');
    }
    // TODO: 添加多租戶 Trait 或 Global Scope
}
