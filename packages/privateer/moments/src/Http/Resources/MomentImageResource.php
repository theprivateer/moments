<?php

namespace Privateer\Moments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MomentImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->glideUrl(800),
            'position' => $this->sort_order,
        ];
    }
}
