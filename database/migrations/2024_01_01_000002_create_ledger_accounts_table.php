<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_device_id')->constrained()->onDelete('cascade');
            $table->enum('chain', ['bitcoin', 'ethereum', 'solana', 'polkadot', 'cardano']);
            $table->string('account_name')->nullable();
            $table->string('derivation_path');
            $table->string('public_address')->unique();
            $table->text('public_key')->nullable();
            $table->integer('account_index');
            $table->boolean('is_active')->default(true);
            $table->text('abi')->nullable();
            $table->json('balance')->nullable();
            $table->timestamp('last_balance_check')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('chain');
            $table->index('public_address');
            $table->index('ledger_device_id');
            $table->unique(['ledger_device_id', 'chain', 'derivation_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};

