<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewPostRequest;
use App\Models\Post;
use Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class PostController extends Controller
{
    public function index()
    {
        
    }

    public function store(NewPostRequest $request)
    {
        $post = Post::create($request->validated());
        return response()->json(["message"=> "Created Post {$post['id']}"]);
    }

    public function show($postId, $authorId)
    {
        // If no author ID given, get posts from this user
        $authorId = !empty($authorId) ?: auth()->user()->id;

        // There should be a check to see if the post is private or not and if the user asking has permission to see.
        // If no post ID given, get all posts
        $post = !empty($postId) ? Post::findOrFail($postId) : Post::where("author_id", $authorId)->get()->toArray();
        
        return $post;
    }
}