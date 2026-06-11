<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NovelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'cover_image' => $this->cover_image ? \App\Models\Novel::storageUrl($this->cover_image) : null,
            'background_image' => $this->background_image ? \App\Models\Novel::storageUrl($this->background_image) : null,
            'author_name' => $this->author_name,
            'views' => $this->views,
            'average_rating' => round($this->ratings_avg_score ?? 0, 1),
            'chapters_count' => $this->chapters_count ?? $this->chapters()->count(),
            'is_adult' => $this->is_adult ?? false,
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name])),
            'publisher' => $this->whenLoaded('publisher', fn() => [
                'id' => $this->publisher->id,
                'name' => $this->publisher->name,
                'avatar' => $this->publisher->avatar_url ?? null,
            ]),
            'volumes' => $this->whenLoaded('volumes', fn() => $this->volumes->map(fn($v) => [
                'id' => $v->id,
                'title' => $v->title,
                'chapters' => $v->chapters->map(fn($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'sort_order' => $c->sort_order,
                ]),
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
