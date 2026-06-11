<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar_url,
            'bio' => $this->bio,
            'novels_count' => $this->novels_count ?? $this->novels()->where('is_published', true)->count(),
            'social_link' => $this->social_link,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
