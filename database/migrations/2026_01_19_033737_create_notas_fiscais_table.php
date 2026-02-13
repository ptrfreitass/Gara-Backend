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
        Schema::create('nota_fiscais', function (Blueprint $table) {
        $table->id();
        
        // Dados de Identificação
        $table->string('chave_acesso', 44)->unique()->nullable();
        $table->string('url_origem')->nullable();
        
        // O "Petróleo Bruto"
        $table->longText('html_bruto'); 
        
        // Dados Processados (JSON no Postgres é excelente para isso)
        $table->json('dados_cabecalho')->nullable(); // Nome do mercado, CNPJ, Data
        $table->json('itens_processados')->nullable(); // A lista de produtos extraídos
        
        // Controle de Estado
        $table->enum('status', ['pendente', 'processado', 'erro'])->default('pendente');
        $table->text('erro_log')->nullable();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};
