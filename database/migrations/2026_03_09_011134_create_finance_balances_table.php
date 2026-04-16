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
        Schema::create('finance_balances', function (Blueprint $table) {
            $table->id();

            $table->decimal('total_income', 10, 2)->default(0);
            $table->decimal('total_expense', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_balances');
    }
};
