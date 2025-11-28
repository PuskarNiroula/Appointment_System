<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index():View{
        $posts = Post::all();
        return view('Post.post',compact('posts'));
    }

    public function create():View{
        return view('Post.create_post');
    }
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:posts,name'
        ]);

        try {
            Post::create([
                'name' => $request->name
            ]);

            return response()->json([
                'status' => "success",
                'message' => 'Post Created Successfully'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Something went wrong!',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit():View{
        return view('Post.update_post');
    }
}
