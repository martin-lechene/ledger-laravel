<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_account_id')->constrained()->onDelete('cascade');
            $table->string('tx_hash')->unique()->nullable();
            $table->string('chain');
            $table->enum('type', ['send', 'receive', 'contract_interaction', 'sign_message']);
            $table->enum('status', ['pending', 'signed', 'submitted', 'confirmed', 'failed']);
            $table->string('from_address');
            $table->string('to_address')->nullable();
            $table->string('amount')->nullable();
            $table->string('token_symbol')->nullable();
            $table->string('token_address')->nullable();
            $table->json('gas_data')->nullable();
            $table->text('raw_data')->nullable();
            $table->text('signed_data')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index('chain');
            $table->index('status');
            $table->index('from_address');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_transactions');
    }
};

