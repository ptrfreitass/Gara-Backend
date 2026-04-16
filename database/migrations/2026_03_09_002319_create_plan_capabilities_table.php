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
        Schema::create('plan_capabilities', function (Blueprint $table) {
            $table->id();
            
            $table->string('plan_type'); // free, plus, premium

            $table->foreignId('capability_id')
                ->constrained('capabilities')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['plan_type', 'capability_id'], 'plan_capability_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_capabilities');
    }
};
