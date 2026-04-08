<?php

namespace Privateer\Moments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

/**
 * @extends Factory<MomentImage>
 */
class MomentImageFactory extends Factory
{
    protected $model = MomentImage::class;

    public function definition(): array
    {
        return [
            'moment_id' => Moment::factory(),
            'path' => 'moments/fake.jpg',
            'disk' => 'public',
            'sort_order' => 1,
            'alt_text' => null,
        ];
    }
}
