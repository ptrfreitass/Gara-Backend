<?php
// app/Services/Finance/Parsers/ParserFactory.php

namespace App\Services\Finance\Parsers;

use App\Models\Bank;
use InvalidArgumentException;

class ParserFactory
{
    // Map: bank code → parser class
    private const PARSERS = [
        '260' => NubankParser::class, // Nubank
    ];

    public static function make(int $bankId): BankParserInterface
    {
        $bank = Bank::findOrFail($bankId);

        $parserClass = self::PARSERS[$bank->code] ?? null;

        if (!$parserClass) {
            throw new InvalidArgumentException(
                "Nenhum parser disponível para o banco: {$bank->name}"
            );
        }

        return new $parserClass($bankId);
    }

    public static function supportedBankIds(): array
    {
        $codes = array_keys(self::PARSERS);
        return Bank::whereIn('code', $codes)
            ->pluck('id')
            ->toArray();
    }
}