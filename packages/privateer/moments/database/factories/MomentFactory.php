<?php

namespace Privateer\Moments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Support\Moments as MomentsSupport;

/**
 * @extends Factory<Moment>
 */
class MomentFactory extends Factory
{
    protected $model = Moment::class;

    public function definition(): array
    {
        $userModel = MomentsSupport::userModel();

        return [
            'user_id' => $userModel::factory(),
            'body' => fake()->paragraphs(2, true),
        ];
    }

    public function withoutBody(): static
    {
        return $this->state(fn (): array => [
            'body' => null,
        ]);
    }
}
