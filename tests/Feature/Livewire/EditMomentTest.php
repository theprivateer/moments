<?php

use App\Livewire\EditMoment;
use App\Models\Moment;
use App\Models\MomentImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders with the existing moment body', function () {
    $moment = Moment::factory()->create(['body' => 'Original body']);

    Livewire::actingAs($moment->user)
        ->test(EditMoment::class, ['moment' => $moment])
        ->assertOk()
        ->assertSet('body', 'Original body');
});

it('returns 403 for a non-owner', function () {
    $moment = Moment::factory()->create();
    $other = User::factory()->create();

    Livewire::actingAs($other)
        ->test(EditMoment::class, ['moment' => $moment])
        ->assertForbidden();
});

it('saves an updated body', function () {
    $moment = Moment::factory()->create(['body' => 'Old']);

    Livewire::actingAs($moment->user)
        ->test(EditMoment::class, ['moment' => $moment])
        ->set('body', 'Updated')
        ->call('save');

    expect($moment->fresh()->body)->toBe('Updated');
});

it('fails when all existing images are removed and no body or new image provided', function () {
    $moment = Moment::factory()->withoutBody()->create();
    $image = MomentImage::factory()->for($moment)->create();

    Livewire::actingAs($moment->user)
        ->test(EditMoment::class, ['moment' => $moment])
        ->set('body', '')
        ->set('imagesToRemove', [$image->id])
        ->call('save')
        ->assertHasErrors(['body']);
});

it('allows save with no body when an existing image is kept', function () {
    $moment = Moment::factory()->withoutBody()->create();
    MomentImage::factory()->for($moment)->create();

    Livewire::actingAs($moment->user)
        ->test(EditMoment::class, ['moment' => $moment])
        ->set('body', '')
        ->call('save')
        ->assertHasNoErrors();
});

it('removes flagged images from storage on save', function () {
    Storage::fake('public');

    $moment = Moment::factory()->create(['body' => 'Keep body']);
    $image = MomentImage::factory()->for($moment)->create(['path' => 'moments/remove.jpg', 'disk' => 'public']);
    Storage::disk('public')->put('moments/remove.jpg', 'fake');

    Livewire::actingAs($moment->user)
        ->test(EditMoment::class, ['moment' => $moment])
        ->set('imagesToRemove', [$image->id])
        ->call('save');

    Storage::disk('public')->assertMissing('moments/remove.jpg');
    expect(MomentImage::find($image->id))->toBeNull();
});

it('stores a newly uploaded image on save', function () {
    Storage::fake('public');

    $moment = Moment::factory()->create(['body' => 'Hello']);

    Livewire::actingAs($moment->user)
        ->test(EditMoment::class, ['moment' => $moment])
        ->set('newImages', [UploadedFile::fake()->image('new.jpg')])
        ->call('save');

    expect($moment->images()->count())->toBe(1);
    Storage::disk('public')->assertExists($moment->images()->first()->path);
});

it('allows save with a new image and no body', function () {
    Storage::fake('public');

    $moment = Moment::factory()->withoutBody()->create();

    Livewire::actingAs($moment->user)
        ->test(EditMoment::class, ['moment' => $moment])
        ->set('body', '')
        ->set('newImages', [UploadedFile::fake()->image('new.jpg')])
        ->call('save')
        ->assertHasNoErrors();
});

it('removes a pending new image from the array', function () {
    Storage::fake('public');

    $moment = Moment::factory()->create(['body' => 'Hello']);
    $file1 = UploadedFile::fake()->image('a.jpg');
    $file2 = UploadedFile::fake()->image('b.jpg');

    Livewire::actingAs($moment->user)
        ->test(EditMoment::class, ['moment' => $moment])
        ->set('newImages', [$file1, $file2])
        ->call('removeNewImage', 0)
        ->assertSet('newImages', fn ($images) => count($images) === 1);
});
