{!! Form::open(['action' => ['UsersController@update_customer_spam_filter', auth()->user()->id], 'method' => 'GET', 'enctype' => 'multipart/form-data', 'style'=>'background-color:lightgray']) !!}
    {{Form::hidden('user_id',auth()->user()->id)}}
    <h2 class="float-left"><strong>App Configuration</strong></h2>
    {{Form::submit('Save App Config', ['class' => "btn btn-success float-right", 'name' => 'submit', 'id' => 'edit-config'])}}
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <td style="width:18%" class="text-center"><h4><strong>Has SPAM filter</strong></h4></td>
                <td class="text-left">
                    <label class="switch"><input type="checkbox" name="has_spam_filter" value=true {{App\Config::first()->has_spam_filter ? 'checked' : ''}}><span class="slider round"></span></label>
                </td>
            </tr>
        </table>
        <table class="table table-bordered" id="customer" name="customer">
            <thead>
                <th style="width:18%" class="text-center"><h4><strong>Customer</strong></h4></th>
                <th style="width:250px" class="text-left"><h4><strong>Domain Suffix &nbsp; &nbsp; &nbsp; E.g. email address &nbsp; &nbsp;  <i> tm1234@ch.ibm.com </i> &nbsp; &nbsp;  has suffix = &nbsp; &nbsp; <i> ch.ibm.com </i> &nbsp; &nbsp;  after symbol @</strong></h4></th>
            </thead>
            @foreach (App\Customer::all() as $customer)
                <tr>
                    <td style="width:18%" class="text-center"><h5><strong><input style="width:100px" class="text-center" type="textbox" name="customer_name_{{$customer->id}}" value='{{$customer->name}}'></strong></h5></td>
                    <td class="text-left"><h5><strong><input style="width:250px" class="text-left" type="textbox" name="customer_domain_{{$customer->id}}" value='{{$customer->domain}}'></strong></h5></td>
                    {{-- TODO : with Vue.js make delete button for customer --}}
                    {{-- <td> --}}
                        {{-- {!!Form::open(['action' => ['TicketsController@destroy', $ticket->id], 'method' => 'POST', 'class' => 'float-right'])!!} --}}
                            {{-- {{Form::hidden('_method', 'DELETE')}} --}}
                            {{-- {{Form::submit('Delete', ['class' => 'btn btn-danger' , 'name' => 'submit_del', 'onclick' => "return  confirm('Do you want to delete the customer $customer->name ?')"])}} --}}
                        {{-- {!!Form::close()!!} --}}
                    {{-- </td> --}}
                </tr>
            @endforeach
            <tr>
                <td class="text-center">{{Form::text('create_customer_name', '', ['style'=>"width:100px", 'placeholder' => 'Name'])}}</td>
                <td>{{Form::text('create_customer_domain', '', ['style'=>"width:250px", 'placeholder' => 'Domain'])}} <b style="color:blue">insert new customer here</b></td>
                {{-- TODO : with Vue.js make button to save new customer --}}
                {{-- <td>{{Form::submit('Save new customer', ['class' => "btn btn-success", 'name' => 'save_new_customer'])}}</td> --}}
            </tr>
        </table>
    </div>
{!! Form::close() !!}