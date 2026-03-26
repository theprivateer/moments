<?php

namespace Privateer\Moments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MomentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'body_html' => $this->renderedBody(),
            'created_at' => $this->created_at,
            'images' => MomentImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
