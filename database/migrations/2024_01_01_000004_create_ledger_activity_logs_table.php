<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_device_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ledger_account_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('chain')->nullable();
            $table->enum('status', ['success', 'failed', 'warning']);
            $table->text('details')->nullable();
            $table->text('error_details')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('user_agent')->nullable();
            $table->timestamps();

            $table->index('action');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_activity_logs');
    }
};

