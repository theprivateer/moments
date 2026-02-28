<?php

namespace Database\Factories;

use App\Models\Moment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MomentImage>
 */
class MomentImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'moment_id' => Moment::factory(),
            'path' => 'moments/fake.jpg',
            'disk' => 'public',
        ];
    }
}
