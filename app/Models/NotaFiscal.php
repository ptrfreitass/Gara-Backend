<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaFiscal extends Model
{
    protected $fillable = [
        'chave_acesso',
        'url_origem',
        'html_bruto',
        'dados_cabecalho',
        'itens_processados',
        'status',
        'erro_log'
    ];

    /**
     * Casts: Converte automaticamente o JSON do banco para tipos PHP
     */
    protected $casts = [
        'dados_cabecalho' => 'array',
        'itens_processados' => 'array',
    ];

    /**
     * Accessor: Permite obter o valor total de itens de forma fácil
     * Exemplo de uso: $nota->total_itens
     */
    public function getTotalItensAttribute()
    {
        return count($this->itens_processados ?? []);
    }

    /**
     * Accessor: Soma o valor total da nota a partir dos itens processados
     * Exemplo de uso: $nota->valor_total_calculado
     */
    public function getValorTotalCalculadoAttribute()
    {
        return collect($this->itens_processados)->sum('valor_total_item');
    }
}