<?php
// database/migrations/xxxx_create_finance_import_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_import_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                ->constrained('finance_import_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Dados originais do extrato (imutáveis)
            $table->string('original_description');
            $table->decimal('original_amount', 10, 2); // positivo=receita, negativo=despesa
            $table->date('original_date');
            $table->string('external_id')->nullable(); // UUID do banco (deduplicação)

            // Tipo detectado automaticamente
            $table->enum('detected_type', ['income','expense','transfer'])->nullable();

            // Campos editáveis pelo usuário na revisão
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
            $table->string('description')->nullable(); // descrição editada

            // Regra aplicada automaticamente (se houver)
            $table->foreignId('matched_rule_id')
                ->nullable()->constrained('finance_import_rules')->nullOnDelete();

            $table->enum('status', ['pending','confirmed','skipped'])->default('pending');

            // FK reversa para a transaction criada (após confirmação)
            $table->foreignId('transaction_id')
                ->nullable()->constrained('finance_transactions')->nullOnDelete();

            $table->timestamps();

            $table->index(['session_id', 'status']);
            $table->unique(['session_id', 'external_id']); // evita duplicatas por UUID
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_import_items');
    }
};