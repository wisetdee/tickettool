<?php

namespace App\Http\Controllers;

use App\User;
use App\Config;
use App\Customer;
use Illuminate\Http\Request;

class UsersController extends Controller
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
        if(!auth()->user()->is_admin){
            return redirect('/home')->with('error','Unautorized Page');
        }
        return view('users.index');
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
        if(!auth()->user()->is_admin){
            return redirect('/home')->with('error','Unautorized Page');
        }
        // validate User Email Address
        if (null == $request->input('create_user_email')) {
            return redirect('users/'.auth()->user()->id)->with('error','User Email Address must not be empty !');
        }
        $email = $request->input('create_user_email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect('users/'.auth()->user()->id)->with('error','There is something wrong with email address : ' . $email);
        }
        // validate User Name
        if (null == $request->input('create_user_name')) {
            return redirect('users/'.auth()->user()->id)->with('error','User Name must not be empty !');
        }
        $name  = $request->input('create_user_name');
        if(empty(trim($name))){
            return redirect('users/'.auth()->user()->id)->with('error','User name must not be empty');
        }
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->is_activated = 1;
        $user->is_notify = 1;
        $user->password = 'no_password';
        $user->save();
        return redirect('users/'.auth()->user()->id)->with('success','New User : "'. $name .'" was created successfully !');
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
        // switch($request->submit) {   // 2 submit button in one form does not work
        //     case 'create-user': 
        //         dd('NEW USER CREATED');
        //         break;
                
        //     case 'edit-user': 
        //         dd('User right edited');
        //         break;
        // }

        if(!auth()->user()->is_admin){
            return redirect('/home')->with('error','Unautorized Page');
        }
        foreach (User::where('id','>',1)->get() as $user){
            $user->name = $request->input("user_name_$user->id");
            $user->email = $request->input("user_email_$user->id");
            $user->is_activated   = $request->input('is_activated_'.$user->id) ? 1 : 0;
            $user->is_admin   = $request->input('is_admin_'.$user->id) ? 1 : 0;
            $user->is_spoc    = $request->input('is_spoc_'.$user->id)  ? 1 : 0;
            $user->is_notify  = $request->input('is_notify_'.$user->id) ? 1 : 0;
            $user->not_notify_my_action = $request->input('not_notify_my_action_'.$user->id) ? 1 : 0;
            $user->save();
        }
        return redirect('users/'.auth()->user()->id)->with('success','Updated in Admin Panel was successfull !');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_customer_spam_filter(Request $request)
    {
        // dd($request->all());
        $config = Config::first();
        $config->has_spam_filter = $request->input('has_spam_filter') ? 1 : 0;
        $config->save();
        foreach ( $request->all() as $name => $value) {
            if (strpos($name,'customer_name_') === 0){
                $customer_id = substr($name, strlen('customer_name_'));
                $customer = Customer::where('id', $customer_id)->get()->first();
                $customer->name = $request->input($name);
                $customer->save();
            }
            if (strpos($name,'customer_domain_') === 0){
                $customer_id = substr($name, strlen('customer_domain_'));
                $customer = Customer::where('id', $customer_id)->get()->first();
                $customer->domain = $request->input($name);
                $customer->save();
            }
            if ( 
                  null != $request->input('create_customer_name')
                & !empty(trim($request->input('create_customer_name')))
                ){
                if(
                      null == $request->input('create_customer_domain')
                    | empty(trim($request->input('create_customer_domain')))
                ){
                    return redirect('/users')->with('error','Customer domain must not be empty!');
                } else {
                    $customer = new Customer();
                    $customer->name = $request->input('create_customer_name');
                    $customer->domain = $request->input('create_customer_domain');
                    $customer->save();
                    return redirect('/users')->with('success','New customer '.$customer->name.' is successfully created');
                }
            } elseif (
                null != $request->input('create_customer_domain')
                & !empty(trim($request->input('create_customer_domain')))
                ){
                return redirect('/users')->with('error','Customer name must not be empty!');
            }
        }
        return redirect('/users')->with('success','Update customers and spam filter was successful');
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
