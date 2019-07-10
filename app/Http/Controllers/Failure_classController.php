<?php

namespace App\Http\Controllers;

use App\Failure_class;
use Illuminate\Http\Request;

class Failure_classController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!auth()->user()->is_admin) {
            return redirect('/home')->with('error','Unautorized Page');
        }
        $failures = [];
        foreach(Failure_class::all() as $fclass)  {
            $this->validate($request, [
                'solution_hour_' . $fclass->id => 'required|numeric|gte:0',
                'warning_hour_' . $fclass->id => 'required|numeric|gte:0',
            ]);
            if($request->input('solution_hour_'.$fclass->id) < $request->input('warning_hour_' . $fclass->id)){
                array_push($failures , $fclass->id);
            } else {
                $fclass->solution_hour = $request->input('solution_hour_'.$fclass->id);
                $fclass->warning_hour = $request->input('warning_hour_'.$fclass->id);
                $fclass->save();
            }
        }
        if(!empty($failures)) {
            return redirect('users/'. auth()->user()->id)->with('error','Error at Failure Class ' . implode($failures,' , ') . '. Solution time must not be less than warning time !');
        }
        return redirect('users/'.auth()->user()->id)->with('success','Update SLA time was successfull !');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
