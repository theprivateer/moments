<?php

namespace Privateer\Moments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use League\Glide\Urls\UrlBuilderFactory;
use Privateer\Moments\Database\Factories\MomentImageFactory;
use Privateer\Moments\Support\Moments as MomentsSupport;

class MomentImage extends Model
{
    use HasFactory;

    protected $fillable = ['moment_id', 'path', 'disk', 'alt_text'];

    protected static function newFactory()
    {
        return MomentImageFactory::new();
    }

    public function moment(): BelongsTo
    {
        return $this->belongsTo(MomentsSupport::momentModel(), 'moment_id');
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
