<?php

namespace Privateer\Moments\Policies;

use Illuminate\Contracts\Auth\Authenticatable;

class MomentPolicy
{
    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, object $moment): bool
    {
        return $this->owns($user, $moment);
    }

    public function delete(Authenticatable $user, object $moment): bool
    {
        return $this->owns($user, $moment);
    }

    /**
     * Both sides are cast because PDO returns bigint columns as strings on
     * PostgreSQL and on MySQL with emulated prepares, which would make a
     * strict comparison against the integer auth identifier always fail.
     */
    protected function owns(Authenticatable $user, object $moment): bool
    {
        return (int) $user->getAuthIdentifier() === (int) $moment->user_id;
    }
}
