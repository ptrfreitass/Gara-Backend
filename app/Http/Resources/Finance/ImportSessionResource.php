<?php
// app/Http/Resources/Finance/ImportSessionResource.php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'bank'           => [
                'id'   => $this->bank->id,
                'name' => $this->bank->name,
                'code' => $this->bank->code,
            ],
            'filename'       => $this->filename,
            'status'         => $this->status,
            'total_rows'     => $this->total_rows,
            'confirmed_rows' => $this->confirmed_rows,
            'skipped_rows'   => $this->skipped_rows,
            'pending_count'  => $this->whenLoaded('items', fn() =>
                $this->items->where('status', 'pending')->count()
            ),
            'items'          => ImportItemResource::collection(
                $this->whenLoaded('items')
            ),
            'created_at'     => $this->created_at->toISOString(),
        ];
    }
}