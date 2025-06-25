<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema; # 修正語法
return new class extends Migration {
    public function up(): void {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('租戶ID'); # 新增 tenant_id
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('phone')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('locations'); # 修正語法
    }
};
