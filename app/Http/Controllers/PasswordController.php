<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;

class PasswordController extends Controller
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

    public function update(Request $request, $id){
        $this->validate($request, [
            'new_password' => 'min:8'
        ]);
        $current_password  = $request->input('current_password');
        $confirm_new_password  = $request->input('confirm_new_password');
        $new_password  = $request->input('new_password');
        $user = User::find($id);
        if(Hash::check($current_password, $user->password)) {
            if( $new_password == $confirm_new_password ) {
                $user->password = Hash::make($new_password);
                $user->save();
                return redirect()->action('PasswordController@index')->with('success', 'Password updated');
            } else {
                return redirect()->action('PasswordController@index')->withErrors('Confirm New Password failed');
            }
        } else {
            return redirect()->action('PasswordController@index')->withErrors('Current Password incorrect');
        }
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('auth.passwords.changepassword');
    }

}
