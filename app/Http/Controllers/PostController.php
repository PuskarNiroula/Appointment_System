<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\Post;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class PostController extends Controller
{
    public function index():View{
        return view('Post.post');
    }

    /**
     * @throws Exception
     */
    public function getPosts():JsonResponse{
        $posts = Post::select('name','status','id');

        return DataTables::of($posts)
            ->addIndexColumn()
            ->make(true);
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

    public function edit(int $id):View{
        $post=Post::select('id','name')->findOrFail( $id);
        return view('Post.update_post',compact('post'));
    }
    public function update(int $id,Request $request):JsonResponse{
        $post=Post::findOrFail($id);
        $request->validate([
            'name'=>'required|string|max:255|unique:posts,name'
        ]);
        $post->name=$request->name;
        $post->save();
        return response()->json([
            'status'=>"success",
            'message'=>"Post Updated Successfully"
        ]);
    }

    public function activate(int $id):JsonResponse{
        Post::findOrFail($id)->update(['status' => 'active']);
        return response()->json([
            'status'=>"success",
            'message'=>"Post Activated Successfully"
        ]);
    }
    public function deactivate(int $id):JsonResponse{
      if(Officer::where('post_id',$id)->where('status',"active")->exists()){
          return response()->json([
              'status'=>"error",
              'message'=>"Post Deactivated Failed, There are officer(s) assigned to this post."
          ]);
      }
      Post::findOrFail($id)->update(['status' => 'inactive']);
      return response()->json([
          'status'=>"success",
          'message'=>"Post Deactivated Successfully"
      ]);
    }

}
