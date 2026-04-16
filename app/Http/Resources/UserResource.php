<?php
// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'username'        => $this->username,
            'email'           => $this->email,
            'email_verified'  => $this->hasVerifiedEmail(),
            'plan_type'       => $this->plan_type->value, // enum para string
            'capabilities'    => $this->capabilities() ?? [],
            'created_at'      => $this->created_at->toISOString(),
        ];
    }
}