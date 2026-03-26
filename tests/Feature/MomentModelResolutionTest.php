<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Privateer\Moments\Models\Moment as BaseMoment;
use Privateer\Moments\Models\MomentImage as BaseMomentImage;
use Privateer\Moments\MomentsServiceProvider;
use Privateer\Moments\Policies\MomentPolicy;
use Privateer\Moments\Support\Moments as MomentsSupport;

class TestMoment extends BaseMoment
{
    protected $table = 'moments';
}

class TestMomentImage extends BaseMomentImage
{
    protected $table = 'moment_images';
}

it('uses the configured moment model for the host user relationship', function () {
    config()->set('moments.moment_model', TestMoment::class);
    config()->set('moments.moment_image_model', TestMomentImage::class);

    $user = User::factory()->create();

    TestMoment::query()->create([
        'user_id' => $user->id,
        'body' => 'Configured moment',
    ]);

    expect($user->moments()->getModel())->toBeInstanceOf(TestMoment::class);
    expect($user->fresh()->moments->first())->toBeInstanceOf(TestMoment::class);
});

it('uses the configured moment image model for moment relationships', function () {
    config()->set('moments.moment_model', TestMoment::class);
    config()->set('moments.moment_image_model', TestMomentImage::class);

    $user = User::factory()->create();

    $moment = TestMoment::query()->create([
        'user_id' => $user->id,
        'body' => 'Configured image relation',
    ]);

    TestMomentImage::query()->create([
        'moment_id' => $moment->id,
        'path' => 'moments/test.jpg',
        'disk' => 'public',
    ]);

    expect($moment->images()->getModel())->toBeInstanceOf(TestMomentImage::class);
    expect($moment->fresh()->images->first())->toBeInstanceOf(TestMomentImage::class);
    expect($moment->fresh()->images->first()->moment)->toBeInstanceOf(TestMoment::class);
});

it('registers the policy against the configured moment model', function () {
    config()->set('moments.moment_model', TestMoment::class);
    config()->set('moments.moment_image_model', TestMomentImage::class);

    app()->register(MomentsServiceProvider::class);

    expect(Gate::getPolicyFor(TestMoment::class))->toBeInstanceOf(MomentPolicy::class);
});

it('uses configured moment subclasses across route-driven package flows', function () {
    config()->set('moments.moment_model', TestMoment::class);
    config()->set('moments.moment_image_model', TestMomentImage::class);

    app()->register(MomentsServiceProvider::class);

    $user = User::factory()->create();
    $moment = TestMoment::query()->create([
        'user_id' => $user->id,
        'body' => 'Rendered from subclass',
    ]);

    TestMomentImage::query()->create([
        'moment_id' => $moment->id,
        'path' => 'moments/route-test.jpg',
        'disk' => 'public',
    ]);

    $this->actingAs($user)
        ->get("/moments/{$moment->id}")
        ->assertSuccessful()
        ->assertSee('Rendered from subclass');

    expect(MomentsSupport::momentModel())->toBe(TestMoment::class);
    expect(MomentsSupport::momentImageModel())->toBe(TestMomentImage::class);
});
