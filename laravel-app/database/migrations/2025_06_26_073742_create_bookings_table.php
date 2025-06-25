<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support->Facades->Schema; # 修正語法
return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('租戶ID'); # 新增 tenant_id
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('pickup_location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('return_location_id')->constrained('locations')->onDelete('cascade');
            $table->dateTime('pickup_datetime');
            $table->dateTime('return_datetime');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'rented', 'returned', 'cancelled'])->default('pending');
            $table->string('payment_status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('bookings');
    }
};
