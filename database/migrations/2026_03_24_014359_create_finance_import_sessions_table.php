<?php
// database/migrations/xxxx_create_finance_import_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_import_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained('banks')->restrictOnDelete();
            $table->string('filename');
            $table->enum('status', ['pending','reviewing','completed','cancelled'])->default('pending');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('confirmed_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_import_sessions');
    }
};