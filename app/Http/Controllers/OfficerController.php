<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\Post;
use App\Models\WorkDay;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class OfficerController extends Controller
{
    public function index():view{
        return view('Officer.index');
    }
    public function create():View{
        $posts=Post::select('id','name')->where('status','active')->orderBy('name')->get();
        return view('Officer.create_officer',compact('posts'));
    }
    public function edit(int $id): View
    {
        $officer = Officer::findOrFail($id);
        $posts = Post::select('id','name')
            ->where('status','active')
            ->orderBy('name')
            ->get();

        return view('Officer.update_officer', compact('officer','posts'));
    }

    public function activate(int $id):JsonResponse{
        $officer=Officer::findOrFail($id);
        if(Post::findOrFail($officer->post_id)->status=='inactive'){
            return response()->json([
                'status'=>'error',
                'message'=>'Post is inactive, cannot activate officer.'
            ]);
        }
        Officer::findOrFail($id)->update(['status'=>'active']);
        return response()->json([
            'status'=>'success',
            'message'=>'Officer Activated Successfully'
        ]);
    }
    public function deactivate(int $id):JsonResponse{
        Officer::findOrFail($id)->update(['status'=>'inactive']);
        return response()->json([
            'status'=>'success',
            'message'=>'Officer Deactivated Successfully'
        ]);
    }

    /**
     * @throws Exception
     */
    public function getOfficers(): JsonResponse
    {
        $officers = Officer::select('id', 'name', 'status','post_id','work_start_time','work_end_time')
            ->with([
                'post' => function ($q) {
                    $q->select('id', 'name');
                }
            ])->with([
                'workDay'=>function ($q){
                $q->select('officer_id','day_of_week');
                }
            ])
            ->get();

        return DataTables::of($officers)
            ->addIndexColumn()
            ->make(true);
    }

    public function store(Request $request):JsonResponse{
        $request->validate([
            'name'=>'required|string|max:255',
            'post_id'=>'required|exists:posts,id',
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i|after:start_time'
        ]);
        try{
            Officer::create([
                'name'=>$request->name,
                'post_id'=>$request->post_id,
                "work_start_time"=>$request->start_time,
                "work_end_time"=>$request->end_time,
            ]);
            return response()->json([
                'status'=>'success',
                'message'=>'Officer Created Successfully'
            ]);
        }catch (Exception $e){
            return response()->json([
                'status'=>'error',
                'message'=>$e->getMessage()
            ]);
        }
    }
    public function update(int $id, Request $request):JsonResponse{
        $officer=Officer::findOrFail($id);
        $request->validate([
            'name'=>'required|string|max:255',
            'post_id'=>'required|exists:posts,id',
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i|after:start_time'
        ]);
        try{
            $officer->update([
                'name'=>$request->name,
                'post_id'=>$request->post_id,
                "work_start_time"=>$request->start_time,
                "work_end_time"=>$request->end_time,
            ]);
            return response()->json([
                'status'=>'success',
                'message'=>'Officer Updated Successfully'
            ]);

        }catch (Exception $e){
            return response()->json([
                'status'=>'error',
                'message'=>$e->getMessage()
            ]);
        }
    }
    public function assignDays(int $id):View{
        $officer = Officer::findOrFail($id);

        $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
        $existingDays = WorkDay::where('officer_id', $id)->pluck('day_of_week')->toArray()??[];


        return view('Officer.assign_working_days', compact('officer','days','existingDays'));
    }
    public function saveWorkingDays(int $id, Request $request):RedirectResponse{

        $request->validate([
            'days' => 'required|array',
            'days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);
        try{
            WorkDay::where('officer_id', $id)->delete();

            $insertData = [];
            foreach ($request->days as $day) {
                $insertData[] = [
                    'officer_id' => $id,
                    'day_of_week' => $day,
                ];
            }

            WorkDay::insert($insertData);
            return redirect()->route('officers.index')->with('success','Working days updated successfully.');
        }catch (Exception $e){
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
}
