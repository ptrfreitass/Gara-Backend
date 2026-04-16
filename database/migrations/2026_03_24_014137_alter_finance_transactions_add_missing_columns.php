<?php
// database/migrations/2026_03_24_014137_alter_finance_transactions_add_missing_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_transactions', 'finance_account_id')) {
                $table->foreignId('finance_account_id')
                    ->nullable()->after('subcategory_id')
                    ->constrained('finance_accounts')->nullOnDelete();
            }

            if (!Schema::hasColumn('finance_transactions', 'credit_card_id')) {
                $table->foreignId('credit_card_id')
                    ->nullable()->after('finance_account_id')
                    ->constrained('credit_cards')->nullOnDelete();
            }

            if (!Schema::hasColumn('finance_transactions', 'credit_card_invoice_id')) {
                $table->foreignId('credit_card_invoice_id')
                    ->nullable()->after('credit_card_id')
                    ->constrained('credit_card_invoices')->nullOnDelete();
            }

            if (!Schema::hasColumn('finance_transactions', 'transfer_to_account_id')) {
                $table->foreignId('transfer_to_account_id')
                    ->nullable()->after('credit_card_invoice_id')
                    ->constrained('finance_accounts')->nullOnDelete();
            }

            if (!Schema::hasColumn('finance_transactions', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('type');
            }

            if (!Schema::hasColumn('finance_transactions', 'status')) {
                $table->string('status')->default('completed')->after('payment_method');
            }

            if (!Schema::hasColumn('finance_transactions', 'external_id')) {
                $table->string('external_id')->nullable()->after('status');
            }
        });

        DB::statement("ALTER TABLE finance_transactions DROP CONSTRAINT IF EXISTS finance_transactions_type_check");
        DB::statement("ALTER TABLE finance_transactions ADD CONSTRAINT finance_transactions_type_check CHECK (type IN ('income','expense','transfer'))");
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('finance_transactions', 'transfer_to_account_id')) {
                $table->dropForeign(['transfer_to_account_id']);
                $table->dropColumn('transfer_to_account_id');
            }

            if (Schema::hasColumn('finance_transactions', 'external_id')) {
                $table->dropColumn('external_id');
            }

            if (Schema::hasColumn('finance_transactions', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('finance_transactions', 'payment_method')) {
                $table->dropColumn('payment_method');
            }

            if (Schema::hasColumn('finance_transactions', 'credit_card_invoice_id')) {
                $table->dropForeign(['credit_card_invoice_id']);
                $table->dropColumn('credit_card_invoice_id');
            }

            if (Schema::hasColumn('finance_transactions', 'credit_card_id')) {
                $table->dropForeign(['credit_card_id']);
                $table->dropColumn('credit_card_id');
            }

            if (Schema::hasColumn('finance_transactions', 'finance_account_id')) {
                $table->dropForeign(['finance_account_id']);
                $table->dropColumn('finance_account_id');
            }
        });

        DB::statement("ALTER TABLE finance_transactions DROP CONSTRAINT IF EXISTS finance_transactions_type_check");
        DB::statement("ALTER TABLE finance_transactions ADD CONSTRAINT finance_transactions_type_check CHECK (type IN ('income','expense'))");
    }
};