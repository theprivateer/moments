<?php

namespace Privateer\Moments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Privateer\Moments\Database\Factories\MomentFactory;
use Privateer\Moments\Markdown\HashtagMarkdownRenderer;
use Privateer\Moments\Support\Moments as MomentsSupport;
use Spatie\Tags\HasTags;

class Moment extends Model
{
    use HasFactory;
    use HasTags;

    protected $fillable = ['user_id', 'body'];

    protected static function newFactory()
    {
        return MomentFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MomentsSupport::userModel(), 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MomentsSupport::momentImageModel(), 'moment_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function renderedBody(): ?string
    {
        return app(HashtagMarkdownRenderer::class)->render($this->body);
    }

    /**
     * Single-line plain text title used by every syndication feed.
     *
     * Entities are decoded because the rendered body is already HTML-escaped;
     * leaving them encoded would escape a second time when the value reaches
     * a Blade template or a JSON payload.
     */
    public function feedTitle(): string
    {
        if (blank($this->body)) {
            return 'Moment - '.$this->created_at->format('j M Y');
        }

        $plainText = html_entity_decode(
            strip_tags((string) $this->renderedBody()),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        return (string) Str::of($plainText)->squish()->limit(60);
    }
}
