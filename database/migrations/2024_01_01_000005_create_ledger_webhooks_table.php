<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('event');
            $table->enum('status', ['pending', 'delivered', 'failed']);
            $table->json('payload');
            $table->string('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('attempt')->default(1);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->index(['event', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_webhooks');
    }
};

