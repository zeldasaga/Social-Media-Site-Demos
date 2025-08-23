<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Privacy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Str;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use RefreshDatabase;

public function test_user_can_create_a_public_post()
{
    $user = User::factory(User::class)->create();
    $this->actingAs($user);

    $this->json("POST", route("make-post"), [
        "author_id" => $user->name,
        "text" => "A test post",
        "post_privacy" => Privacy::Public
    ]);

    $this->assertDatabaseHas('posts', [
        'author_id' => $user->name,
        'text' => "A test post",
        'post_privacy' => 'public',
    ]);
}

public function test_guest_cannot_create_a_public_post()
{
    $postText = Str::random(234);
    $this->json("POST", route("make-post"), [
        "author_id" => "Suzy Should Not",
        "text" => $postText,
        "post_privacy" => Privacy::Public
    ])->assertStatus(401);

    $this->assertDatabaseMissing('posts', [
        'author_id' => "Suzy Should Not",
        'text' => $postText,
        'post_privacy' => 'public',
    ]);
}
    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAs($user = User::factory()->create());

        $this->get('/dashboard')->assertOk();
    }
}
