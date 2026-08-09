<?php

use App\Models\User;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Policies\MomentPolicy;

/*
 * PDO returns bigint columns as strings on PostgreSQL, and on MySQL with
 * emulated prepares. SQLite returns integers, so these tests pin the type
 * coercion that keeps ownership checks working on the other drivers.
 */

it('authorises the owner when user_id arrives as a string', function () {
    $user = User::factory()->create();
    $moment = (object) ['user_id' => (string) $user->id];
    $policy = new MomentPolicy;

    expect($policy->update($user, $moment))->toBeTrue()
        ->and($policy->delete($user, $moment))->toBeTrue();
});

it('denies a non-owner when user_id arrives as a string', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $moment = (object) ['user_id' => (string) $owner->id];
    $policy = new MomentPolicy;

    expect($policy->update($other, $moment))->toBeFalse()
        ->and($policy->delete($other, $moment))->toBeFalse();
});

it('casts user_id to an integer on the moment model', function () {
    $moment = Moment::factory()->create();

    $moment->user_id = (string) $moment->user_id;

    expect($moment->user_id)->toBeInt();
});

it('lets the owner edit and delete their moment over http', function () {
    $user = User::factory()->create();
    $moment = Moment::factory()->for($user)->create();

    $this->actingAs($user)->get("/moments/{$moment->id}/edit")->assertSuccessful();
    $this->actingAs($user)->delete("/moments/{$moment->id}")->assertRedirect('/');

    $this->assertModelMissing($moment);
});
