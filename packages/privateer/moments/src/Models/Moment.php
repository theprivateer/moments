<?php

namespace Privateer\Moments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
}
