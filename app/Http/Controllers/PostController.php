<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
    
        $posts = Post::where('user_id', $user->id)
            ->orderBy('data', 'desc')
            ->get();
    
        return response()->json($posts);
    }

    public function store(StorePostRequest $request)
{
    $user = auth()->user();

    $post = new Post();
    $post->description = $request->description;
    $post->data = now();
    $post->user_id = $user->id;

    if ($request->hasFile('picture')) {
        $path = $request->file('picture')->store('postagens', 'public');
        $post->picture = '/storage/' . $path;
    }

    $post->save();

    return response()->json($post, 201);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.

     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
