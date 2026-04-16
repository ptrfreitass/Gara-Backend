<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('bank_id')
                ->nullable()
                ->constrained('banks')
                ->nullOnDelete();

            // Conta corrente vinculada para débito da fatura
            $table->foreignId('finance_account_id')
                ->nullable()
                ->constrained('finance_accounts')
                ->nullOnDelete();

            $table->string('name');
            $table->string('last_four_digits', 4)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('available_credit', 15, 2)->default(0);
            $table->tinyInteger('closing_day');  // 1-31
            $table->tinyInteger('due_day');      // 1-31
            $table->string('color', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
