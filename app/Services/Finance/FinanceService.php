<?php

namespace App\Services\Finance;

use App\Models\CreditCard;
use App\Models\FinanceBalance;
use App\Models\FinanceCategory;
use App\Models\FinanceSubcategory;
use App\Models\FinanceTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    public function __construct(protected InvoiceService $invoiceService) {}

    // -------------------------
    // Categorias
    // -------------------------

    public function getCategories(User $user)
    {
        return FinanceCategory::where('user_id', $user->id)
            ->with('subcategories')
            ->orderBy('name')
            ->get();
    }

    public function createCategory(User $user, array $data): FinanceCategory
    {
        return FinanceCategory::create([
            'user_id' => $user->id,
            'name'    => $data['name'],
            'type'    => $data['type'],
            'color'   => $data['color'] ?? null,
        ]);
    }

    public function deleteCategory(User $user, int $id): void
    {
        FinanceCategory::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->delete();
    }

    // -------------------------
    // Subcategorias
    // -------------------------

    public function createSubcategory(User $user, array $data): FinanceSubcategory
    {
        FinanceCategory::where('id', $data['category_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        return FinanceSubcategory::create([
            'user_id'     => $user->id,
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'color'       => $data['color'] ?? null,
        ]);
    }

    public function deleteSubcategory(User $user, int $id): void
    {
        FinanceSubcategory::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->delete();
    }

    // -------------------------
    // Transações
    // -------------------------

    public function getTransactions(User $user, array $filters = [])
    {
        $query = FinanceTransaction::where('user_id', $user->id)
            ->with(['category', 'subcategory', 'financeAccount', 'creditCard'])
            ->orderByDesc('date');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
        if (!empty($filters['finance_account_id'])) {
            $query->where('finance_account_id', $filters['finance_account_id']);
        }
        if (!empty($filters['credit_card_id'])) {
            $query->where('credit_card_id', $filters['credit_card_id']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->paginate(20);
    }

    public function createTransaction(User $user, array $data): FinanceTransaction
    {
        return DB::transaction(function () use ($user, $data) {
            $creditCardInvoiceId = null;

            // Resolve fatura se pagamento for crédito
            if (($data['payment_method'] ?? null) === 'credit' && !empty($data['credit_card_id'])) {
                $card = CreditCard::where('id', $data['credit_card_id'])
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $invoice = $this->invoiceService->resolveInvoice(
                    $card,
                    Carbon::parse($data['date'])
                );

                $creditCardInvoiceId = $invoice->id;
            }

            return FinanceTransaction::create([
                'user_id'                => $user->id,
                'category_id'            => $data['category_id'],
                'subcategory_id'         => $data['subcategory_id'] ?? null,
                'finance_account_id'     => $data['finance_account_id'] ?? null,
                'credit_card_id'         => $data['credit_card_id'] ?? null,
                'credit_card_invoice_id' => $creditCardInvoiceId,
                'amount'                 => $data['amount'],
                'description'            => $data['description'] ?? null,
                'date'                   => $data['date'],
                'type'                   => $data['type'],
                'payment_method'         => $data['payment_method'] ?? null,
                'status'                 => $data['status'] ?? 'completed',
            ]);
            // Observer cuida do saldo automaticamente
        });
    }

    public function deleteTransaction(User $user, int $id): void
    {
        FinanceTransaction::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->delete();
        // Observer cuida do saldo automaticamente
    }

    // -------------------------
    // Saldo
    // -------------------------

    public function getBalance(User $user): FinanceBalance
    {
        return FinanceBalance::firstOrCreate(
            ['user_id' => $user->id],
            ['total_income' => 0, 'total_expense' => 0, 'balance' => 0]
        );
    }
}