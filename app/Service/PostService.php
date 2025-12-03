<?php
namespace App\Service;

use App\Models\Post;
use Exception;

class PostService{
    public function getActivePostNameAndId(){
        return Post::where('status', 'active')
            ->orderBy('name')
            ->pluck('id', 'name');
    }
    public function getPostQuery(){
       return Post::select('name','status','id');
    }
    public function createPost(string $name):array{
       if(post::create([
           'name'=>$name
       ])->exists()){
           return [
               'status'=>"success",
               'message'=>"Post Created Successfully"
           ];
       }
       return [
           'status'=>"error",
           'message'=>"Post Creation Failed"
       ];
    }

    /**
     * @throws Exception
     */
    public function getPostById(int $id): ?Post
    {
        $post=Post::find($id);
        if(!$post)
           throw new Exception("Post Not Found");
        return $post;
    }


    public function updatePost(int $id,string $name):array{

       try{
           $post=$this->getPostById($id);
           $post->update(['name'=>$name]);
           return [
               'status'=>"success",
               'message'=>"Post Updated Successfully"
           ];
       }
       catch (Exception $e){
           return [
               'status'=>"error",
               'message'=>$e->getMessage()
           ];
       }
    }
    public function activatePost(int $id):array{
        try{
            $post=$this->getPostById($id);
            $post->update(['status'=>'active']);
            return [
                'status'=>"success",
                'message'=>"Post Activated Successfully"
            ];
        }catch (Exception $e){
            return [
                'status'=>"error",
                'message'=>$e->getMessage()
            ];
        }
    }
    public function deactivatePost(int $id):array{
        try{
            $post=$this->getPostById($id);
            $post->update(['status'=>'inactive']);
            return [
                'status'=>"success",
                'message'=>"Post Deactivated Successfully"
            ];
        }catch (Exception $e){
            return [
                'status'=>"error",
                'message'=>$e->getMessage()
            ];
        }
    }
}
