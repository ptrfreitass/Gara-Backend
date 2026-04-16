<?php
// database/migrations/2026_03_22_000006_create_debts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('finance_account_id')
                ->nullable()
                ->constrained('finance_accounts')
                ->nullOnDelete();

            $table->foreignId('credit_card_id')
                ->nullable()
                ->constrained('credit_cards')
                ->nullOnDelete();

            $table->string('creditor_name');
            $table->string('description')->nullable();
            $table->enum('type', [
                'loan', 'financing', 'credit_card',
                'boleto', 'personal', 'other'
            ]);
            $table->enum('payment_method', [
                'cash', 'pix', 'debit', 'credit', 'ted', 'boleto', 'other'
            ]);
            $table->decimal('original_amount', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->decimal('interest_rate', 5, 4)->nullable(); // ex: 0.0199 = 1.99%
            $table->smallInteger('total_installments')->nullable();
            $table->smallInteger('paid_installments')->default(0);
            $table->date('start_date');
            $table->date('due_date');
            $table->enum('status', [
                'active', 'paid', 'overdue', 'negotiating'
            ])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};