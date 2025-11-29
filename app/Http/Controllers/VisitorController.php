<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class VisitorController extends Controller
{
    public function index():View{
        return view('Visitor.visitor');
    }

    /**
     * @throws Exception
     */
    public function getVisitors():JsonResponse{
        return DataTables::of(Visitor::query())->addIndexColumn()->make();
    }

    public function activate(int $id):JsonResponse{
        try{
            Visitor::findOrFail($id)->update(['status' => 'active']);
            return response()->json([
                'status'=>"success",
                'message'=>"Visitor Activated Successfully"
            ]);
        }catch (Exception $e){
            return response()->json([
                'status'=>"error",
                'message'=>$e->getMessage()
            ]);
        }
    }
    public function deactivate(int $id):JsonResponse{
        try{
            Visitor::findOrFail($id)->update(['status' => 'inactive']);
            return response()->json([
                'status'=>"success",
                'message'=>"Visitor Deactivated Successfully"
            ]);
        }catch (Exception $e){
            return response()->json([
                'status'=>"error",
                'message'=>$e->getMessage()
            ]);
        }
    }
    public function create():View{
        return view('Visitor.create_visitor');
    }
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email'=>'required|email|unique:visitors,email',
            'mobile_number'=>'required|numeric|unique:visitors,mobile_num',
        ]);
        try{
            Visitor::create([
                'name'=>$request->name,
                'email'=>$request->email,
                "mobile_num"=>$request->mobile_number,
            ]);
            return response()->json([
                'status'=>"success",
                'message'=>"Visitor Created Successfully"
            ]);

        }catch (Exception $e){
            return response()->json([
                'status'=>"error",
                'message'=>$e->getMessage()
            ]);
        }

    }
    public function edit(int $id):View{
        $visitor=Visitor::findOrFail($id);
        return view('Visitor.update_visitor',compact('visitor'));
    }
    public function update(int $id,Request $request):JsonResponse
    {
        $visitor=Visitor::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email', 'unique:visitors,email,' . $visitor->id
            ],
            'mobile_number' => ['required','numeric','unique:visitors,mobile_num,'.$visitor->id],
        ]);
        try {
            $visitor->update([
                'name' => $request->name,
                'email' => $request->email,
                "mobile_num" => $request->mobile_number,
            ]);
            return response()->json([
                'status' => "success",
                'message' => "Visitor updated Successfully"
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage()
            ]);
        }
    }
}
