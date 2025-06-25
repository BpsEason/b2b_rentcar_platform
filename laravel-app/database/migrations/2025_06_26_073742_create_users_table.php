<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema; # 修正語法
return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('租戶ID，用於多租戶隔離'); # 新增 tenant_id
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            # 如果租戶ID為空表示為平台級用戶或單租戶模式
            # $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade'); # 如果有 tenants 表，則解除註解
        });
    }
    public function down(): void {
        Schema::dropIfExists('users');
    }
};
