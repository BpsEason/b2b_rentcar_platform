<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate->Support->Facades->Schema; # 修正語法
return new class extends Migration {
    public function up(): void {
        Schema::create('rental_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('租戶ID'); # 新增 tenant_id
            $table->string('vehicle_type')->nullable(); # 例如: 'economy', 'suv', 'luxury'
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('cascade');
            $table->decimal('base_daily_rate', 8, 2);
            $table->json('dynamic_factors')->nullable(); # JSON 欄位，用於存儲動態定價因子 (例如: ['peak_season_multiplier' => 1.2])
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            # 添加聯合唯一索引以避免重複的費率規則
            $table->unique(['vehicle_type', 'vehicle_id', 'start_date', 'end_date', 'tenant_id'], 'unique_rental_rate');
        });
    }
    public function down(): void {
        Schema::dropIfExists('rental_rates');
    }
};
