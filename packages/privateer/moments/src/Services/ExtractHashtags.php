<?php

namespace Privateer\Moments\Services;

use Illuminate\Support\Str;

class ExtractHashtags
{
    /**
     * @return list<string>
     */
    public function extract(?string $body): array
    {
        if ($body === null || trim($body) === '') {
            return [];
        }

        preg_match_all('/(?<![\pL\pN_])#([A-Za-z0-9_][A-Za-z0-9_-]*)(?![A-Za-z0-9_-])/u', $body, $matches);

        /** @var list<string> $hashtags */
        $hashtags = collect($matches[1] ?? [])
            ->map(fn (string $tag): string => Str::lower($tag))
            ->unique()
            ->values()
            ->all();

        return $hashtags;
    }

    public function matchStartingAt(string $line, ?string $previousCharacter = null): ?string
    {
        if ($previousCharacter !== null && preg_match('/[\pL\pN_]/u', $previousCharacter) === 1) {
            return null;
        }

        if (preg_match('/^#([A-Za-z0-9_][A-Za-z0-9_-]*)(?![A-Za-z0-9_-])/u', $line, $matches) !== 1) {
            return null;
        }

        return Str::lower($matches[1]);
    }
}
