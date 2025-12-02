<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Service\VisitorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

 class VisitorController extends Controller
{
    protected VisitorService $service;

    public function __construct(VisitorService $service){
        $this->service=$service;
    }
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
        if($this->service->activateVisitor($id)){
            return response()->json([
                'status'=>"success",
                'message'=>"Visitor Activated Successfully"
            ]);
        }
        return response()->json([
            'status'=>"error",
            'message'=>"Visitor Activated Failed"
        ]);
    }
    public function deactivate(int $id):JsonResponse{
        if($this->service->deactivateVisitor($id)){
            return response()->json([
                'status'=>"success",
                'message'=>"Visitor Deactivated Successfully"
            ]);
        }
        return response()->json([
            'status'=>"error",
            'message'=>"Visitor Deactivated Failed"
        ]);

    }
    public function create():View{
        return view('Visitor.create_visitor');
    }
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email'=>'required|email|unique:visitors,email',
            'mobile_number'=>'required|numeric|digits:10|unique:visitors,mobile_num',
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
            'mobile_number' => ['required','digits:10','numeric','unique:visitors,mobile_num,'.$visitor->id],
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
