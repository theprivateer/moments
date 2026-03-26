<?php

namespace Privateer\Moments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use League\Glide\Urls\UrlBuilderFactory;
use Privateer\Moments\Database\Factories\MomentImageFactory;

class MomentImage extends Model
{
    use HasFactory;

    protected $fillable = ['moment_id', 'path', 'disk'];

    protected static function newFactory(): MomentImageFactory
    {
        return MomentImageFactory::new();
    }

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function glideUrl(int $width): string
    {
        $builder = UrlBuilderFactory::create(url('/img/'), config('moments.glide_sign_key'));

        return $builder->getUrl($this->path, ['w' => $width, 'disk' => $this->disk]);
    }
}
