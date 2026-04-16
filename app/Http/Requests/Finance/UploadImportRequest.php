<?php
// app/Http/Requests/Finance/UploadImportRequest.php

namespace App\Http\Requests\Finance;

use App\Services\Finance\Parsers\ParserFactory;
use Illuminate\Foundation\Http\FormRequest;

class UploadImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file'    => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // 5MB
            'bank_id' => ['required', 'integer', 'exists:banks,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $bankId = (int) $this->input('bank_id');
            $supported = ParserFactory::supportedBankIds();

            if (!in_array($bankId, $supported)) {
                $validator->errors()->add(
                    'bank_id',
                    'Este banco ainda não possui um parser de extrato disponível.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Selecione um arquivo CSV.',
            'file.mimes'    => 'O arquivo deve ser um CSV.',
            'file.max'      => 'O arquivo não pode ultrapassar 5MB.',
            'bank_id.exists'=> 'Banco inválido.',
        ];
    }
}