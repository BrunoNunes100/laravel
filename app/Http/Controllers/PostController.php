<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Listar todas as postagens do usuário logado
     */
    public function index()
    {
        $user = Auth::user();
        $posts = Post::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($post) {
                return [
                    'id' => $post->id,
                    'description' => $post->description,
                    'picture' => $post->picture ? url('storage/postagens/'.$post->picture) : null,
                    'data' => $post->created_at,
                ];
            });

        return response()->json($posts);
    }

    /**
     * Criar nova postagem
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'description' => 'required|string|max:255',
            'picture' => 'nullable|image|max:10240', // até 10MB
        ]);

        $post = new Post();
        $post->user_id = $user->id;
        $post->description = $request->input('description');

        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->storeAs('public/postagens', $filename);
            $post->picture = $filename;
        }

        $post->save();

        return response()->json([
            'id' => $post->id,
            'description' => $post->description,
            'picture' => $post->picture ? url('storage/postagens/'.$post->picture) : null,
            'data' => $post->created_at,
        ]);
    }
}
