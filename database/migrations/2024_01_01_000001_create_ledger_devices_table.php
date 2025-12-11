<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('model')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->enum('transport_type', ['usb', 'bluetooth', 'webusb']);
            $table->string('device_path')->nullable();
            $table->string('name')->nullable();
            $table->text('firmware_version')->nullable();
            $table->json('capabilities')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('transport_type');
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_devices');
    }
};

