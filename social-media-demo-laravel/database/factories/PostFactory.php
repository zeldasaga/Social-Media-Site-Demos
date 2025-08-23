<?php

namespace Database\Factories;

use App\Models\Post;
use App\Privacy;
use Auth;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => Auth::getUser()->id,
            'text' => Str::random(254),
            'post_privacy' => Privacy::Public,
        ];
    }
}
