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
        Schema::create('credit_card_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('credit_card_id')
                ->constrained('credit_cards')
                ->cascadeOnDelete();

            $table->tinyInteger('reference_month'); // 1-12
            $table->smallInteger('reference_year');
            $table->date('opening_date');
            $table->date('closing_date');
            $table->date('due_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', ['open', 'closed', 'paid', 'overdue'])->default('open');
            $table->timestamps();

            // Uma fatura por cartão por mês
            $table->unique(['credit_card_id', 'reference_month', 'reference_year']);
            $table->index(['credit_card_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_card_invoices');
    }
};
