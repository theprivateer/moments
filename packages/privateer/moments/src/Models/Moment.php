<?php

namespace Privateer\Moments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Privateer\Moments\Database\Factories\MomentFactory;
use Privateer\Moments\Support\Moments as MomentsSupport;

class Moment extends Model
{
    use HasFactory;

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
        return $this->hasMany(MomentsSupport::momentImageModel(), 'moment_id');
    }

    public function renderedBody(): ?string
    {
        if ($this->body === null) {
            return null;
        }

        return Str::markdown($this->body, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
