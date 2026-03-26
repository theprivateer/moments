<?php

namespace Privateer\Moments\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Privateer\Moments\Models\Moment;

class MomentPolicy
{
    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, Moment $moment): bool
    {
        return (int) $user->getAuthIdentifier() === $moment->user_id;
    }

    public function delete(Authenticatable $user, Moment $moment): bool
    {
        return (int) $user->getAuthIdentifier() === $moment->user_id;
    }
}
