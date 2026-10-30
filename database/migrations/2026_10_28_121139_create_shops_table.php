<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('status_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('shop_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50);
            $table->string('recipient_name', 100);
            $table->string('phone', 30);
            $table->string('province', 100);
            $table->string('city', 100);
            $table->string('postal_code', 10);
            $table->text('address_line');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('shop_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_status_histories');
        Schema::dropIfExists('shop_addresses');
        Schema::dropIfExists('shops');
    }
};
