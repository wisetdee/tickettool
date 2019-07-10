<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Spent_time;

class SpentTimesController extends Controller
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
        // return $spent_times = Post::where('title', 'Post Two')->get();
        // $spent_times = DB::select('SELECT * FROM posts');
        // $spent_times = Post::orderBy('created_at','desc')->take(1)->get();
        // $spent_times = Post::orderBy('created_at','desc')->get();
        // $spent_times = Post::orderBy('created_at','desc')->paginate(10);
        // return view('spent_times.index')->with('ticket_id',$ticket_id);
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
        $this->validate($request, [
            'spent_time_hour' => 'required|between:0,99.99',
        ]);
        $spent_time = new Spent_time();
        $spent_time->ticket_id = $request->input('ticket_id');
        $spent_time->user_id = auth()->user()->id;
        $spent_time->hour = $request->input('spent_time_hour');
        $spent_time->comment = $request->input('spent_time_comment');
        $spent_time->save();
        return redirect('/tickets/'.$spent_time->ticket_id)->with('success','Spent Time recorded'); 
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $spent_time = Spent_time::find($id);
        $ticket_id = $spent_time->ticket->id;

        // Check for correct user
        if(!auth()->user()->is_admin) {
            if(auth()->user()->id != $spent_time->user_id){
                return redirect('/tickets/'.$ticket_id)->with('error','Unautorized Page');
            }
        }
        $spent_time->delete();    
        return redirect('/tickets/'.$ticket_id)->with('success','Spent Time Removed');
    }
}
