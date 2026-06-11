<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'novel_id' => $this->novel_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'is_locked' => $this->is_locked ?? false,
            'published_at' => $this->published_at?->toIso8601String(),
            'novel' => $this->whenLoaded('novel', fn() => [
                'id' => $this->novel->id,
                'title' => $this->novel->title,
                'slug' => $this->novel->slug,
                'cover_image' => $this->novel->cover_image ? \App\Models\Novel::storageUrl($this->novel->cover_image) : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
