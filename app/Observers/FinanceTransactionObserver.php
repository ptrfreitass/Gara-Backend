<?php

namespace App\Observers;

use App\Models\FinanceTransaction;
use App\Models\FinanceBalance;
use App\Models\FinanceAccount;
use App\Models\CreditCard;

class FinanceTransactionObserver
{
    public function created(FinanceTransaction $transaction): void
    {
        if ($transaction->status !== 'completed') return;

        $this->updateBalance($transaction, 'add');
        $this->updateAccountBalance($transaction, 'add');
        $this->updateCreditCard($transaction, 'add');
    }

    public function updated(FinanceTransaction $transaction): void
    {
        // Status mudou para completed
        if ($transaction->wasChanged('status')) {
            if ($transaction->status === 'completed') {
                $this->updateBalance($transaction, 'add');
                $this->updateAccountBalance($transaction, 'add');
                $this->updateCreditCard($transaction, 'add');
            } elseif ($transaction->getOriginal('status') === 'completed') {
                $this->updateBalance($transaction, 'remove');
                $this->updateAccountBalance($transaction, 'remove');
                $this->updateCreditCard($transaction, 'remove');
            }
        }

        // Valor ou tipo mudou (apenas se completed)
        if ($transaction->status === 'completed' && $transaction->wasChanged(['amount', 'type'])) {
            // Reverte o original
            $original = new FinanceTransaction($transaction->getOriginal());
            $this->updateBalance($original, 'remove');
            $this->updateAccountBalance($original, 'remove');
            $this->updateCreditCard($original, 'remove');

            // Aplica o novo
            $this->updateBalance($transaction, 'add');
            $this->updateAccountBalance($transaction, 'add');
            $this->updateCreditCard($transaction, 'add');
        }
    }

    public function deleted(FinanceTransaction $transaction): void
    {
        if ($transaction->status !== 'completed') return;

        $this->updateBalance($transaction, 'remove');
        $this->updateAccountBalance($transaction, 'remove');
        $this->updateCreditCard($transaction, 'remove');
    }

    // -------------------------
    // Finance Balance (legado — mantém compatibilidade)
    // -------------------------

    private function updateBalance(FinanceTransaction $transaction, string $action): void
    {
        $balance = FinanceBalance::firstOrCreate(
            ['user_id' => $transaction->user_id],
            ['total_income' => 0, 'total_expense' => 0, 'balance' => 0]
        );

        $amount = (float) $transaction->amount;
        $multiplier = $action === 'add' ? 1 : -1;
       
        if ($transaction->type === 'income') {
            $balance->increment('total_income', $amount * $multiplier);
            $balance->increment('balance', $amount * $multiplier);
        } elseif ($transaction->type === 'expense') {
            $balance->increment('total_expense', $amount * $multiplier);
            $balance->increment('balance', -$amount * $multiplier);
        }
    }

    // -------------------------
    // Saldo da conta financeira
    // -------------------------

    private function updateAccountBalance(FinanceTransaction $transaction, string $action): void
    {
        // Crédito não impacta conta — impacta o cartão
        if ($transaction->payment_method === 'credit') return;
        if (!$transaction->finance_account_id) return;

        $account = FinanceAccount::find($transaction->finance_account_id);
        if (!$account) return;

        $amount = (float) $transaction->amount;
        $multiplier = $action === 'add' ? 1 : -1;

        if ($transaction->type === 'income') {
            $account->increment('current_balance', $amount * $multiplier);
        } elseif ($transaction->type === 'expense' || $transaction->type === 'transfer') {
            $account->increment('current_balance', -$amount * $multiplier);
        }
    }

    // -------------------------
    // Crédito disponível do cartão
    // -------------------------

    private function updateCreditCard(FinanceTransaction $transaction, string $action): void
    {
        if ($transaction->payment_method !== 'credit') return;
        if (!$transaction->credit_card_id) return;
        if ($transaction->type !== 'expense') return;

        $card = CreditCard::find($transaction->credit_card_id);
        if (!$card) return;

        $amount = (float) $transaction->amount;
        $multiplier = $action === 'add' ? 1 : -1;

        // Gasto no crédito reduz crédito disponível
        $card->increment('available_credit', -$amount * $multiplier);

        // Atualiza total da fatura
        if ($transaction->credit_card_invoice_id) {
            $transaction->creditCardInvoice?->increment('total_amount', $amount * $multiplier);
        }
    }
}