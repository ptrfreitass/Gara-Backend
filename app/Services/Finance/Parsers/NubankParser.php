<?php
// app/Services/Finance/Parsers/NubankParser.php

namespace App\Services\Finance\Parsers;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class NubankParser implements BankParserInterface
{
    // ID do Nubank na tabela banks (code: '260')
    private const BANK_CODE = '260';

    private int $resolvedBankId;

    public function __construct(int $bankId)
    {
        $this->resolvedBankId = $bankId;
    }

    public function bankId(): int
    {
        return $this->resolvedBankId;
    }

    public function validate(UploadedFile $file): bool
    {
        if ($file->getClientOriginalExtension() !== 'csv') return false;

        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        fclose($handle);

        // Nubank CSV header: Date,Valor,Identificador,Descrição
        return count($header) === 4;
    }

    public function parse(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        // Pula o header
        fgetcsv($handle);

        $items = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            [$date, $amount, $externalId, $description] = $row;

            // Sanitiza encoding (Nubank exporta com problemas de charset)
            $description = $this->sanitize($description);
            $date        = $this->parseDate($date);
            $amount      = (float) str_replace(',', '.', $amount);

            if (!$date) continue;

            $items[] = [
                'date'         => $date,
                'amount'       => $amount,        // positivo = receita, negativo = despesa
                'description'  => $description,
                'external_id'  => trim($externalId),
            ];
        }

        fclose($handle);

        if (empty($items)) {
            throw ValidationException::withMessages([
                'file' => ['O arquivo CSV não contém transações válidas.']
            ]);
        }

        return $items;
    }

    private function parseDate(string $date): ?string
    {
        // Nubank: DD/MM/YYYY
        $parts = explode('/', trim($date));
        if (count($parts) !== 3) return null;

        [$d, $m, $y] = $parts;
        return sprintf('%04d-%02d-%02d', $y, $m, $d);
    }

    private function sanitize(string $text): string
    {
        // Tenta converter de Latin-1 para UTF-8 se necessário
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }
        return trim($text);
    }
}