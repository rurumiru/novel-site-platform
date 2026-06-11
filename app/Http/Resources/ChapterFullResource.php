<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChapterFullResource extends ChapterResource
{
    public function toArray(Request $request): array
    {
        $arr = parent::toArray($request);
        $arr['content'] = $this->content;
        return $arr;
    }
}
