<?php
// app/Services/Finance/ImportService.php

namespace App\Services\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceImportItem;
use App\Models\FinanceImportRule;
use App\Models\FinanceImportSession;
use App\Services\Finance\Parsers\ParserFactory;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ImportService
{
    public function __construct(protected FinanceService $financeService) {}

    // -------------------------
    // 1. Upload + Parse + Aplicar Regras
    // -------------------------

    public function upload(User $user, UploadedFile $file, int $bankId): FinanceImportSession
    {
        $parser = ParserFactory::make($bankId);

        if (!$parser->validate($file)) {
            throw new \InvalidArgumentException(
                'Arquivo inválido ou incompatível com o banco selecionado.'
            );
        }

        $rows = $parser->parse($file);

        return DB::transaction(function () use ($user, $file, $bankId, $rows) {
            $session = FinanceImportSession::create([
                'user_id'    => $user->id,
                'bank_id'    => $bankId,
                'filename'   => $file->getClientOriginalName(),
                'status'     => 'reviewing',
                'total_rows' => count($rows),
            ]);

            $rules = FinanceImportRule::where('user_id', $user->id)->get();
            $userAccounts = FinanceAccount::where('user_id', $user->id)
                ->where('is_active', true)
                ->get();

            foreach ($rows as $row) {
                $this->createItem($session, $user, $row, $rules, $userAccounts);
            }

            return $session->load('items');
        });
    }

    // -------------------------
    // 2. Atualizar item na revisão
    // -------------------------

    public function updateItem(User $user, int $itemId, array $data): FinanceImportItem
    {
        $item = FinanceImportItem::where('id', $itemId)
            ->where('user_id', $user->id)
            ->whereHas('session', fn($q) => $q->where('status', 'reviewing'))
            ->firstOrFail();

        $item->update([
            'type'                   => $data['type']                   ?? $item->type,
            'category_id'            => $data['category_id']            ?? $item->category_id,
            'subcategory_id'         => $data['subcategory_id']         ?? null,
            'finance_account_id'     => $data['finance_account_id']     ?? $item->finance_account_id,
            'transfer_to_account_id' => $data['transfer_to_account_id'] ?? null,
            'payment_method'         => $data['payment_method']         ?? $item->payment_method,
            'description'            => $data['description']            ?? $item->description,
            'status'                 => $data['status']                 ?? $item->status,
        ]);

        // Salva regra se "lembrar" marcado
        if (!empty($data['remember']) && !empty($data['keyword'])) {
            $this->upsertRule($user, $data['keyword'], $item);
        }

        return $item->fresh(['category', 'subcategory', 'financeAccount', 'transferToAccount', 'matchedRule']);
    }

    // -------------------------
    // 3. Confirmar sessão inteira
    // -------------------------

    public function confirmSession(User $user, int $sessionId): FinanceImportSession
    {
        $session = FinanceImportSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->where('status', 'reviewing')
            ->firstOrFail();

        return DB::transaction(function () use ($user, $session) {
            $confirmed = 0;
            $skipped   = 0;

            $items = $session->items()->where('status', '!=', 'skipped')->get();

            foreach ($items as $item) {
                if ($item->status === 'skipped') {
                    $skipped++;
                    continue;
                }

                // Item sem classificação mínima → pula automaticamente
                if (!$item->type || !$item->finance_account_id) {
                    $item->update(['status' => 'skipped']);
                    $skipped++;
                    continue;
                }

                $transaction = $this->commitItem($user, $item);

                $item->update([
                    'status'         => 'confirmed',
                    'transaction_id' => $transaction->id,
                ]);

                $confirmed++;
            }

            $session->update([
                'status'         => 'completed',
                'confirmed_rows' => $confirmed,
                'skipped_rows'   => $skipped,
            ]);

            return $session->fresh();
        });
    }

    // -------------------------
    // 4. Cancelar sessão
    // -------------------------

    public function cancelSession(User $user, int $sessionId): void
    {
        $session = FinanceImportSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'reviewing'])
            ->firstOrFail();

        $session->update(['status' => 'cancelled']);
    }

    // -------------------------
    // 5. Listar sessões do usuário
    // -------------------------

    public function getSessions(User $user)
    {
        return FinanceImportSession::where('user_id', $user->id)
            ->with('bank')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    // -------------------------
    // 6. Buscar sessão com itens
    // -------------------------

    public function getSession(User $user, int $sessionId): FinanceImportSession
    {
        return FinanceImportSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->with([
                'bank',
                'items.category',
                'items.subcategory',
                'items.financeAccount',
                'items.transferToAccount',
                'items.matchedRule',
            ])
            ->firstOrFail();
    }

    // -------------------------
    // 7. Regras do usuário
    // -------------------------

    public function getRules(User $user)
    {
        return FinanceImportRule::where('user_id', $user->id)
            ->with(['category', 'subcategory', 'financeAccount', 'transferToAccount'])
            ->orderByDesc('match_count')
            ->get();
    }

    public function updateRule(User $user, int $ruleId, array $data): FinanceImportRule
    {
        $rule = FinanceImportRule::where('id', $ruleId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $rule->update([
            'keyword'                => $data['keyword']                ?? $rule->keyword,
            'type'                   => $data['type']                   ?? $rule->type,
            'category_id'            => $data['category_id']            ?? null,
            'subcategory_id'         => $data['subcategory_id']         ?? null,
            'finance_account_id'     => $data['finance_account_id']     ?? null,
            'transfer_to_account_id' => $data['transfer_to_account_id'] ?? null,
            'payment_method'         => $data['payment_method']         ?? null,
        ]);

        return $rule->fresh();
    }

    public function deleteRule(User $user, int $ruleId): void
    {
        FinanceImportRule::where('id', $ruleId)
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->delete();
    }

    // =========================================================
    // Métodos privados
    // =========================================================

    private function createItem(
        FinanceImportSession $session,
        User $user,
        array $row,
        $rules,
        $userAccounts
    ): FinanceImportItem {
        $detectedType = $this->detectType($row['amount']);
        $matchedRule  = $this->matchRule($row['description'], $rules);

        // Detecta transferência interna automaticamente
        if ($detectedType !== 'transfer') {
            $detectedType = $this->detectTransfer($row['description'], $userAccounts) ?? $detectedType;
        }

        $itemData = [
            'session_id'          => $session->id,
            'user_id'             => $user->id,
            'original_description'=> $row['description'],
            'original_amount'     => $row['amount'],
            'original_date'       => $row['date'],
            'external_id'         => $row['external_id'] ?? null,
            'detected_type'       => $detectedType,
            // Pré-preenche com a regra se houver
            'type'                => $matchedRule?->type ?? $detectedType,
            'category_id'         => $matchedRule?->category_id,
            'subcategory_id'      => $matchedRule?->subcategory_id,
            'finance_account_id'  => $matchedRule?->finance_account_id,
            'transfer_to_account_id' => $matchedRule?->transfer_to_account_id,
            'payment_method'      => $matchedRule?->payment_method ?? $this->detectPaymentMethod($row['description']),
            'description'         => $row['description'],
            'matched_rule_id'     => $matchedRule?->id,
            'status'              => 'pending',
        ];

        // Atualiza métricas da regra
        if ($matchedRule) {
            $matchedRule->increment('match_count');
            $matchedRule->update(['last_matched_at' => now()]);
        }

        return FinanceImportItem::create($itemData);
    }

    private function commitItem(User $user, FinanceImportItem $item): \App\Models\FinanceTransaction
    {
        $amount = abs((float) $item->original_amount);
        $date   = $item->original_date->format('Y-m-d');

        if ($item->type === 'transfer') {
            return $this->commitTransfer($user, $item, $amount, $date);
        }

        return $this->financeService->createTransaction($user, [
            'category_id'        => $item->category_id,
            'subcategory_id'     => $item->subcategory_id,
            'finance_account_id' => $item->finance_account_id,
            'amount'             => $amount,
            'description'        => $item->description ?? $item->original_description,
            'date'               => $date,
            'type'               => $item->type,
            'payment_method'     => $item->payment_method,
            'status'             => 'completed',
            'external_id'        => $item->external_id,
            'import_item_id'     => $item->id,
        ]);
    }

    private function commitTransfer(
        User $user,
        FinanceImportItem $item,
        float $amount,
        string $date
    ): \App\Models\FinanceTransaction {
        // Categoria de transferência — busca ou cria automaticamente
        $transferCategory = \App\Models\FinanceCategory::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Transferência'],
            ['type' => 'both', 'color' => '#6366f1']
        );

        $description = $item->description ?? $item->original_description;

        // Saída da conta origem
        $outTransaction = $this->financeService->createTransaction($user, [
            'category_id'            => $transferCategory->id,
            'finance_account_id'     => $item->finance_account_id,
            'transfer_to_account_id' => $item->transfer_to_account_id,
            'amount'                 => $amount,
            'description'            => $description,
            'date'                   => $date,
            'type'                   => 'expense',
            'payment_method'         => 'transfer',
            'status'                 => 'completed',
            'external_id'            => $item->external_id,
            'import_item_id'         => $item->id,
        ]);

        // Entrada na conta destino (se existir)
        if ($item->transfer_to_account_id) {
            $this->financeService->createTransaction($user, [
                'category_id'            => $transferCategory->id,
                'finance_account_id'     => $item->transfer_to_account_id,
                'transfer_to_account_id' => $item->finance_account_id,
                'amount'                 => $amount,
                'description'            => $description,
                'date'                   => $date,
                'type'                   => 'income',
                'payment_method'         => 'transfer',
                'status'                 => 'completed',
                'import_item_id'         => $item->id,
            ]);
        }

        return $outTransaction;
    }

    private function detectType(float $amount): string
    {
        return $amount >= 0 ? 'income' : 'expense';
    }

    private function detectTransfer(string $description, $userAccounts): ?string
    {
        $lower = mb_strtolower($description);

        // Palavras-chave que indicam transferência
        $transferKeywords = ['transferência', 'transferencia', 'pix enviado', 'pix recebido', 'ted', 'doc'];

        foreach ($transferKeywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                // Verifica se algum nome de conta do usuário aparece na descrição
                foreach ($userAccounts as $account) {
                    if (str_contains($lower, mb_strtolower($account->name))) {
                        return 'transfer';
                    }
                }
            }
        }

        return null;
    }

    private function detectPaymentMethod(string $description): ?string
    {
        $lower = mb_strtolower($description);

        if (str_contains($lower, 'pix'))     return 'pix';
        if (str_contains($lower, 'débito') || str_contains($lower, 'debito')) return 'debit';
        if (str_contains($lower, 'crédito') || str_contains($lower, 'credito')) return 'credit';
        if (str_contains($lower, 'ted'))     return 'ted';
        if (str_contains($lower, 'boleto'))  return 'boleto';
        if (str_contains($lower, 'transferência') || str_contains($lower, 'transferencia')) return 'transfer';

        return null;
    }

    private function matchRule(string $description, $rules): ?FinanceImportRule
    {
        foreach ($rules as $rule) {
            if ($rule->matches($description)) {
                return $rule;
            }
        }
        return null;
    }

    private function upsertRule(User $user, string $keyword, FinanceImportItem $item): void
    {
        FinanceImportRule::updateOrCreate(
            ['user_id' => $user->id, 'keyword' => mb_strtolower(trim($keyword))],
            [
                'type'                   => $item->type,
                'category_id'            => $item->category_id,
                'subcategory_id'         => $item->subcategory_id,
                'finance_account_id'     => $item->finance_account_id,
                'transfer_to_account_id' => $item->transfer_to_account_id,
                'payment_method'         => $item->payment_method,
            ]
        );
    }
}