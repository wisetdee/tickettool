@extends('layouts.app')

@section('content')
    <h1>Admin Panel</h1>
    <hr>
        <a href="/tickets" class="btn btn-secondary">cancal</a>
    <hr>
    <div class="table-responsive">
        <table class="table table-bordered" style="background-color:palegreen">
                <td style="width:25%"><h3><strong>Admin User:</strong></h3></td>
                <td style="width:75%"><h3>{{auth()->user()->name}}</h3></td>
        </table>
    </div>
    <hr>
    {{-- <div class="container"> --}}
        <hws></hws>
    {{-- </div> --}}
    <hr>
    @include('users.spam_filter')
    <hr>
    {!! Form::open(['action' => ['UsersController@update', auth()->user()->id], 'method' => 'POST', 'enctype' => 'multipart/form-data', 'style'=>'background-color:lightcyan']) !!}
        {{Form::hidden('user_id',auth()->user()->id)}}
        {{Form::hidden('_method','PUT')}}
        <h2 class="float-left"><strong>Edit User Rights</strong></h2>
        {{Form::submit('Save User Right(s) and Setting(s)', ['class' => "btn btn-success float-right", 'name' => 'submit', 'id' => 'edit-user'])}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <th style="width:18%" class="text-center"><h4><strong>User Name</strong></h4></th>
                    <th style="width:30%" class="text-center"><h4><strong>User Email Address</strong></h4></th>
                    <th style="width:12%" class="text-center"><h4><strong>is activated</strong></h4></th>
                    <th style="width:10%" class="text-center"><h4><strong>is Admin</strong></h4></th>
                    <th style="width:5%" class="text-center"><h4><strong>is SPOC</strong></h4></th>
                    <th style="width:5%" class="text-center"><h4><strong>is notify</strong></h4></th>
                    <th style="width:20%" class="text-center"><h4><strong>Not notify my action</strong></h4></th>
                </thead>
                @foreach (App\User::where('id','>',1)->get() as $user)
                    <tr>
                        <td class="text-center"><h5><strong><input style="width:250px" class="text-center" type="textbox" name="user_name_{{$user->id}}" value='{{$user->name}}'></strong></h5></td>
                        <td class="text-center"><h5><strong><input style="width:400px" class="text-center" type="textbox" name="user_email_{{$user->id}}" value='{{$user->email}}'></strong></h5></td>
                        <td class="text-center">
                            <label class="switch"><input type="checkbox" name="is_activated_{{$user->id}}" value=true {{$user->is_activated ? 'checked' : ''}}><span class="slider round"></span></label>
                        </td>
                        <td class="text-center">
                            <label class="switch"><input type="checkbox" name="is_admin_{{$user->id}}" value=true {{$user->is_admin ? 'checked' : ''}}><span class="slider round"></span></label>
                        </td>
                        <td class="text-center">
                            <label class="switch"><input type="checkbox" name="is_spoc_{{$user->id}}" value=true {{$user->is_spoc ? 'checked' : ''}}><span class="slider round"></span></label>
                        </td>
                        <td class="text-center">
                            <label class="switch"><input type="checkbox" name="is_notify_{{$user->id}}" value=true {{$user->is_notify ? 'checked' : ''}}><span class="slider round"></span></label>
                        </td>
                        <td class="text-center">
                            <label class="switch"><input type="checkbox" name="not_notify_my_action_{{$user->id}}" value=true {{$user->not_notify_my_action ? 'checked' : ''}}><span class="slider round"></span></label>
                        </td>            
                    </tr>
                @endforeach
            </table>
        </div>
    {!! Form::close() !!}
    <hr>
    {!! Form::open(['action' => ['UsersController@store', auth()->user()->id], 'method' => 'POST', 'enctype' => 'multipart/form-data', 'style'=>'background-color:lightyellow']) !!}
        {{Form::hidden('user_id',auth()->user()->id)}}
        {{-- {{Form::hidden('_method','PUT')}} --}}
        <h2 class="float-left"><strong>Create New User</strong></h2>
        {{Form::submit('Save New User', ['class' => "btn btn-success float-right", 'name' => 'submit', 'id' => 'create-user'])}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 35%">{{Form::label('user_name', 'User Name', ['class' => 'h5 font-weight-bold text-center'])}}</th>
                        <th style="width: 65%">{{Form::label('user_mail', 'Email Adress', ['class' => 'h5 font-weight-bold text-center'])}}</th>
                    </tr>
                </thead>
                <tr>
                    <td>{{Form::text('create_user_name', '', ['class' => 'form-control', 'placeholder' => 'User Name'])}}</td>
                    <td>{{Form::text('create_user_email', '', ['class' => 'form-control', 'placeholder' => 'Email Adress'])}}</td>
                </tr>
            </table>
        </div>
    {!! Form::close() !!}
    <hr>
    {!! Form::open(['action' => ['Failure_classController@update', 0], 'method' => 'POST', 'enctype' => 'multipart/form-data', 'style'=>'background-color:lightpink']) !!}
        {{Form::hidden('user_id',auth()->user()->id)}}
        {{Form::hidden('_method','PUT')}}
        <h2 class="float-left"><strong>Edit SLA Times</strong></h2>
        {{Form::submit('Save SLA Times', ['class' => "btn btn-success float-right", 'name' => 'submit', 'id' => 'sla_times'])}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <th style="width:30%" class="text-center"><h4><strong>Failure Class</strong></h4></th>
                    <th style="width:35%" class="text-center"><h4><strong>Solution Time [ hour(s) ]</strong></h4></th>
                    <th style="width:35%" class="text-center"><h4><strong>Warning Time <br> since ticket has started <br> [ hour(s) ]</strong></h4></th>
                </thead>
                @foreach (App\Failure_class::all() as $failure_class)
                    <tr>
                        <td style="width:15%" class="text-center">{{Form::label("failure_class_id_$failure_class->id", $failure_class->id)}}</td>
                        <td style="width:15%" class="text-center"><input class="text-center" type="number" name="solution_hour_{{$failure_class->id}}" value={{$failure_class->solution_hour}}></td>
                        <td style="width:15%" class="text-center"><input class="text-center" type="number" name="warning_hour_{{$failure_class->id}}" value={{$failure_class->warning_hour}}></td>
                    </tr>
                @endforeach
            </table>
        </div>    
    {!! Form::close() !!}
@endsection

@push('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            $("input#edit-user").hide();
            $("input#edit-config").hide();
            $("input#create-user").hide();
            $("input#sla_times").hide();
            var show_button_save_sla_time = function() {
                $("input#sla_times").show();
            }
            var show_button_edit_user = function() {
                $("input#edit-user").show();
            }
            var show_button_edit_config = function() {
                $("input#edit-config").show();
            }
            var show_button_create_user = function() {
                $("input#create-user").show();
            }
            var tag = 'input[type=number]';
            $(tag).click(show_button_save_sla_time);
            $(tag).change(show_button_save_sla_time);
            $(tag).select(show_button_save_sla_time);

            var tag = "input[name^='is_activated_'],[name^='is_admin_'],[name^='is_spoc_'],[name^='is_notify_'],[name^='not_notify_my_action_'],[name^='user_email_'],[name^='user_name_']";
            $(tag).click(show_button_edit_user);
            $(tag).change(show_button_edit_user);
            $(tag).select(show_button_edit_user);
            
            var tag = "input[name^='has_spam_filter'],[name^='customer_name_'],[name^='customer_domain_'],[name^='create_customer_name'],[name^='create_customer_domain']";
            $(tag).click(show_button_edit_config);
            $(tag).change(show_button_edit_config);
            $(tag).select(show_button_edit_config);

            var tag = "[name='create_user_email'],[name='create_user_name']";
            $(tag).click(show_button_create_user);
            $(tag).change(show_button_create_user);
            $(tag).select(show_button_create_user);
            
            //only one spoc must be selected
            var tag = "input[name^='is_spoc_']";
            $(tag).click(function() {
                $(tag).prop('checked', false);
                $(this).prop('checked', true);
                // if($(this).prop('checked')){ // DOES NOT WORK
                //     $(this).prop('checked', false);
                // }else{
                //     $(this).prop('checked', true);
                // }
            });
        });
    </script>
@endpush
