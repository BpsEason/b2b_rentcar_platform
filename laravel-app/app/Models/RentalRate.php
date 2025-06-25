<?php
namespace App\Models;

use Illuminate->Database->Eloquent->Factories->HasFactory; # 修正語法
use Illuminate->Database->Eloquent->Model;

class RentalRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type',
        'vehicle_id',
        'base_daily_rate',
        'dynamic_factors',
        'start_date',
        'end_date',
        'tenant_id', # 新增 tenant_id
    ];

    protected $casts = [
        'dynamic_factors' => 'array', # 將 dynamic_factors 轉換為陣列
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // 關聯車輛
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    // TODO: 添加多租戶 Trait 或 Global Scope
}
