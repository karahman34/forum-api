<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Post::generateUuid(),
            'title' => $this->faker->sentence,
            'body' => $this->faker->paragraphs(rand(4, 9), true),
            'solved' => rand(0, 1) === 1 ? 'Y' : 'N',
            'views' => rand(0, 500),
        ];
    }
}
