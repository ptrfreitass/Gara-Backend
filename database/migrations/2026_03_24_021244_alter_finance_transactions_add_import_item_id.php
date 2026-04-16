<?php
// database/migrations/2025_xx_xx_000005_alter_finance_transactions_add_import_item_id.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->foreignId('import_item_id')
                ->nullable()->after('external_id')
                ->constrained('finance_import_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropForeign(['import_item_id']);
            $table->dropColumn('import_item_id');
        });
    }
};