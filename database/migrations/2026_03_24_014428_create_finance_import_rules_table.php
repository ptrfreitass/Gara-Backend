<?php
// database/migrations/xxxx_create_finance_import_rules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_import_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Keyword extraída da descrição original
            $table->string('keyword'); // ex: "MERCEARIA ABRAAO", "PATRICK FREITAS"

            // Classificação salva
            $table->enum('type', ['income','expense','transfer'])->nullable();
            $table->foreignId('category_id')
                ->nullable()->constrained('finance_categories')->nullOnDelete();
            $table->foreignId('subcategory_id')
                ->nullable()->constrained('finance_subcategories')->nullOnDelete();
            $table->foreignId('finance_account_id')
                ->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->foreignId('transfer_to_account_id')
                ->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->enum('payment_method', ['cash','pix','debit','credit','ted','boleto','transfer','other'])->nullable();

            // Métricas de uso
            $table->unsignedInteger('match_count')->default(0);
            $table->timestamp('last_matched_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'keyword']); // uma regra por keyword por usuário
            $table->index(['user_id', 'match_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_import_rules');
    }
};