<?php
// database/migrations/2026_03_22_000007_create_debt_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('debt_id')
                ->constrained('debts')
                ->cascadeOnDelete();

            // Nullable — nem todo pagamento gera transação registrada
            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('finance_transactions')
                ->nullOnDelete();

            $table->decimal('amount', 15, 2);
            $table->date('paid_at');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('debt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};