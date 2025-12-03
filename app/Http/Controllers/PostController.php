<?php

namespace App\Http\Controllers;

use App\Service\OfficerService;
use App\Service\PostService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class PostController extends Controller
{
    protected PostService $postService;
    protected OfficerService $officerService;
    public function __construct(){
        $this->postService=app(PostService::class);
        $this->officerService=app(OfficerService::class);
    }
    public function index():View{
        return view('Post.post');
    }

    /**
     * @throws Exception
     */
    public function getPosts():JsonResponse{
        $query = $this->postService->getPostQuery();
        return DataTables::of($query)
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

       $response=$this->postService->createPost($request->name);
       return response()->json($response);
    }

    public function edit(int $id):View{
        $post=$this->postService->getPostById($id);
        return view('Post.update_post',compact('post'));
    }
    public function update(int $id,Request $request):JsonResponse{

        $request->validate([
            'name'=>'required|string|max:255|unique:posts,name,'.$id
        ]);
       $response=$this->postService->updatePost($id,$request->name);
       return response()->json($response);
    }

    public function activate(int $id):JsonResponse{
       $response= $this->postService->activatePost($id);
       return response()->json($response);
    }
    public function deactivate(int $id):JsonResponse{
     if($this->officerService->getOfficerByPostId($id)){
         return response()->json([
             'status'=>'error',
             'message'=>'Post has active officers'
         ]);
     }
      $response= $this->postService->deactivatePost($id);
     return response()->json($response);
    }

}
