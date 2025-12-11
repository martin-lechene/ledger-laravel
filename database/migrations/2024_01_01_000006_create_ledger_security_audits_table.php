<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_security_audits', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['pass', 'warning', 'critical']);
            $table->json('findings');
            $table->integer('score')->default(100);
            $table->string('grade')->default('A');
            $table->json('recommendations')->nullable();
            $table->timestamp('performed_at')->default(now());
            $table->timestamps();

            $table->index('status');
            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_security_audits');
    }
};

