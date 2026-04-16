<?php

namespace App\Services\Finance;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Busca ou cria a fatura aberta para o cartão na data da transação.
     */
    public function resolveInvoice(CreditCard $card, Carbon $transactionDate): CreditCardInvoice
    {
        [$month, $year, $openingDate, $closingDate, $dueDate] =
            $this->calculateInvoicePeriod($card, $transactionDate);

        return CreditCardInvoice::firstOrCreate(
            [
                'credit_card_id'  => $card->id,
                'reference_month' => $month,
                'reference_year'  => $year,
            ],
            [
                'opening_date' => $openingDate,
                'closing_date' => $closingDate,
                'due_date'     => $dueDate,
                'total_amount' => 0,
                'paid_amount'  => 0,
                'status'       => 'open',
            ]
        );
    }

    /**
     * Calcula o período da fatura com base no closing_day do cartão.
     */
    private function calculateInvoicePeriod(CreditCard $card, Carbon $date): array
    {
        $closingDay = $card->closing_day;
        $dueDay     = $card->due_day;

        // Se a data da transação é após o fechamento, pertence à próxima fatura
        if ($date->day > $closingDay) {
            $referenceDate = $date->copy()->addMonth();
        } else {
            $referenceDate = $date->copy();
        }

        $month = (int) $referenceDate->month;
        $year  = (int) $referenceDate->year;

        // Abertura = dia seguinte ao fechamento do mês anterior
        $prevClosing  = Carbon::create($year, $month, $closingDay)->subMonth();
        $openingDate  = $prevClosing->copy()->addDay();
        $closingDate  = Carbon::create($year, $month, $closingDay);

        // Vencimento pode ser no mês seguinte ao fechamento
        $dueDate = Carbon::create($year, $month, $dueDay);
        if ($dueDay <= $closingDay) {
            $dueDate->addMonth();
        }

        return [$month, $year, $openingDate, $closingDate, $dueDate];
    }
}