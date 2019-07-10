<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\hw;
use App\Http\Resources\Hw as HwResource;
class HwController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get hws
        $hws = Hw::orderBy('created_at', 'desc')->paginate(5);
        // Return collection of hws as a resource
        return hwResource::collection($hws);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $hw = $request->isMethod('put') ? Hw::findOrFail($request->hw_id) : new Hw;
        $hw->id = $request->input('hw_id');
        $hw->name = $request->input('name');
        if($hw->save()) {
            return new HwResource($hw);
        }
        
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Get hw
        $hw = Hw::findOrFail($id);
        // Return single hw as a resource
        return new HwResource($hw);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Get hw
        $hw = Hw::findOrFail($id);
        if($hw->delete()) {
            return new HwResource($hw);
        }    
    }
}