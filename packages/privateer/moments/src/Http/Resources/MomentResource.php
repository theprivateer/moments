<?php

namespace Privateer\Moments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Privateer\Moments\Support\Moments as MomentsSupport;

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
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag): array => [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'url' => route(MomentsSupport::routeName('tags.show'), ['tag' => $tag->slug]),
            ])->values()->all(), []),
        ];
    }
}
