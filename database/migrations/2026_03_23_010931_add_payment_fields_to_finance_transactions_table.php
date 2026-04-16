<?php
// database/migrations/2026_03_22_000005_add_payment_fields_to_finance_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->foreignId('finance_account_id')
                ->nullable()
                ->after('subcategory_id')
                ->constrained('finance_accounts')
                ->nullOnDelete();

            $table->foreignId('credit_card_id')
                ->nullable()
                ->after('finance_account_id')
                ->constrained('credit_cards')
                ->nullOnDelete();

            $table->foreignId('credit_card_invoice_id')
                ->nullable()
                ->after('credit_card_id')
                ->constrained('credit_card_invoices')
                ->nullOnDelete();

            $table->enum('payment_method', [
                'cash', 'pix', 'debit', 'credit', 'ted', 'boleto', 'other'
            ])->nullable()->after('type');

            $table->enum('status', [
                'pending', 'completed', 'cancelled'
            ])->default('completed')->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropForeign(['finance_account_id']);
            $table->dropForeign(['credit_card_id']);
            $table->dropForeign(['credit_card_invoice_id']);
            $table->dropColumn([
                'finance_account_id',
                'credit_card_id',
                'credit_card_invoice_id',
                'payment_method',
                'status'
            ]);
        });
    }
};