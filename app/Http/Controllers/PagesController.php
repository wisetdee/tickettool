<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
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
    
    public function index(){
        $title = 'Welcome To IBM Ticket!';
        // return view('pages.index', compact('title'));
        return view('pages.index')->with('title',$title);
    }
    
    public function about(){
        $title = 'About IBM SLA';
        return view('pages.about')->with('title',$title);
    }

    public function services(){
        $data = [
            'title' => 'Services',
            'services' => ['Auto reply', 'Ticket statistik']
        ];
        return view('pages.services')->with($data);
    }
}
