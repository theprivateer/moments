<?php

namespace Privateer\Moments\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Privateer\Moments\Models\Moment;

class JsonFeedController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $moments = Moment::query()
            ->with(['user', 'images'])
            ->latest()
            ->limit(20)
            ->get();

        $payload = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => config('app.name'),
            'home_page_url' => route('moments.index'),
            'feed_url' => route('feed.json'),
            'items' => $moments->map(function (Moment $moment): array {
                $title = $moment->body
                    ? Str::limit(strip_tags($moment->renderedBody()), 60)
                    : 'Moment - '.$moment->created_at->format('j M Y');

                $contentHtml = '';

                foreach ($moment->images as $image) {
                    $contentHtml .= '<img src="'.$image->url().'" alt="">';
                }

                if ($moment->body) {
                    $contentHtml .= $moment->renderedBody();
                }

                $item = [
                    'id' => route('moments.show', $moment),
                    'url' => route('moments.show', $moment),
                    'title' => $title,
                    'content_html' => $contentHtml,
                    'date_published' => $moment->created_at->toIso8601String(),
                    'date_modified' => $moment->updated_at->toIso8601String(),
                ];

                if ($moment->images->isNotEmpty()) {
                    $item['image'] = $moment->images->first()->url();
                }

                return $item;
            })->all(),
        ];

        return response()->json($payload)->header('Content-Type', 'application/feed+json');
    }
}
