<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; # 修正語法
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'make',
        'model',
        'license_plate',
        'year',
        'color',
        'status',
        'description',
        'daily_rate',
        'tenant_id', # 新增 tenant_id
    ];

    // 關聯據點
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // 關聯訂單
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    // TODO: 添加多租戶 Trait 或 Global Scope
}
