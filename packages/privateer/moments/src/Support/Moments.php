<?php

namespace Privateer\Moments\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Moments
{
    public static function userModel(): string
    {
        return config('moments.user_model');
    }

    public static function newUserModel(): Model
    {
        $model = static::userModel();

        return new $model;
    }

    public static function userTable(): string
    {
        return static::newUserModel()->getTable();
    }

    public static function firstUser(): ?Authenticatable
    {
        return static::userModel()::query()->first();
    }

    public static function routePrefix(): string
    {
        return static::normalizePrefix(config('moments.route_prefix', 'moments'));
    }

    public static function apiPrefix(): string
    {
        return static::normalizePrefix(config('moments.api_prefix', 'api'));
    }

    public static function routeNamePrefix(): string
    {
        $prefix = trim((string) config('moments.route_name_prefix', ''), '.');

        return $prefix === '' ? '' : $prefix.'.';
    }

    protected static function normalizePrefix(?string $prefix): string
    {
        $prefix = trim((string) $prefix);

        if ($prefix === '' || $prefix === '/') {
            return '';
        }

        return trim($prefix, '/');
    }
}
