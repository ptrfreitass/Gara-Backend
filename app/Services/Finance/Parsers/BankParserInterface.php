<?php
// app/Services/Finance/Parsers/BankParserInterface.php

namespace App\Services\Finance\Parsers;

use Illuminate\Http\UploadedFile;

interface BankParserInterface
{
    /**
     * Retorna o bank_id correspondente ao parser.
     */
    public function bankId(): int;

    /**
     * Parseia o arquivo e retorna array de itens normalizados.
     * Cada item: ['date', 'amount', 'description', 'external_id']
     */
    public function parse(UploadedFile $file): array;

    /**
     * Valida se o arquivo é compatível com este parser.
     */
    public function validate(UploadedFile $file): bool;
}