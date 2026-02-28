<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MomentImage extends Model
{
    use HasFactory;

    protected $fillable = ['moment_id', 'path', 'disk'];

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
