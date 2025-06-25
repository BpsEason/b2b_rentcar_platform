<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate->Database\Schema\Blueprint;
use Illuminate\Support->Facades->Schema; # 修正語法
return new class extends Migration {
    public function up(): void {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('租戶ID'); # 新增 tenant_id
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('make');
            $table->string('model');
            $table->string('license_plate')->unique();
            $table->string('year');
            $table->string('color')->nullable();
            $table->enum('status', ['available', 'rented', 'maintenance', 'unavailable'])->default('available');
            $table->text('description')->nullable();
            $table->decimal('daily_rate', 8, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('vehicles');
    }
};
