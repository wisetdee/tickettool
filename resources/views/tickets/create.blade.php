@extends('layouts.app')

@section('content')
<h1 class="bg-{{$class}} text-center text-{{$text_color}}">{{$title}}</h1>
    {!! Form::open(['action' => 'TicketsController@store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <hr>
        {{Form::submit('Save', ['class' => "btn btn-success"])}}
        {{-- {{Form::hidden('_method','PUT')}} --}}
        <a href="/tickets" class="btn btn-secondary">cancel</a>
        <hr>
        <div class="form-group">
            <br>
            {{Form::text('subject', null, ['class' => 'form-control', 'placeholder' => 'Subject'])}}
            <br>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <td style="width:20%"><h5><strong>Status:         </strong></h5></td>
                        <td style="width:20%">{!! Form::select('status_id', App\Status::where('name','not like','CLOSED')->pluck('name', 'id'), null , ['class' => 'form-control']) !!}</td>
                        <td style="width:20%"><h5><strong>Start Time:     </strong></h5></td>
                        <td style="width:40%">{{  Form::label('dateTime-local', Carbon\Carbon::now() , ['class' => 'form-control']) }}<br><strong style="color:blue">can be changed later</strong></td>
                        {{ Form::hidden('created_at' , Carbon\Carbon::now())}}
                    </tr>
                    <tr>
                        <td><h5><strong>Assignee:       </strong></h5></td><td>{!! Form::select('user_id', App\User::orderBy('name')->where('id','>',1)->pluck('name', 'id'), null , ['class' => 'form-control']) !!}</td>
                        <td><h5><strong>Reaction Time:  </strong></h5></td><td>{{  Form::label('dateTime-local', Carbon\Carbon::now() , ['class' => 'form-control'])}}<br><strong style="color:blue">can be changed later</strong></td>
                        {{ Form::hidden('reacted_at' , Carbon\Carbon::now())}}
                    </tr>
                    <tr>
                        <td><h5><strong>cutomer:        </strong></h5></td><td>{{  Form::text('owner', auth()->user()->name, ['class' => 'form-control', 'placeholder' => 'Owner'])}}</td>
                        <td><h5><strong>End Time:       </strong></h5></td><td>{{  Form::input('dateTime-local', 'closed_at' , null , ['class' => 'form-control'])}}</td>
                    </tr>
                    <tr>
                        <td><h5><strong>Customer Email: </strong></h5></td><td>{{  Form::text('owner_mail', auth()->user()->email   , ['class' => 'form-control', 'placeholder' => 'Owner Email Address'])}}</td>
                        <td><h5><strong>CC Email:       </strong></h5></td><td>{{  Form::textarea('cc_mail', null , ['class' => 'form-control', 'placeholder' => 'CC Email Address'])}}</td>
                    </tr>
                    <tr>
                        <td><h5><strong>IBM SLA Detail:</strong></h5></td>
                        {{-- old solution --}}
                        {{-- <td>
                            <p> {{ Form::radio('sla_yes_no', 1, false)}} Yes</p>
                            <p> {{ Form::radio('sla_yes_no', 0, true) }} No</p>
                        </td> --}}
                        <td style="width:10%" class="text-center"><label class="switch"><input type="checkbox" name="sla_yes_no" value="yes"><span class="slider round"></span></label></td>
                    </tr>
                </table>
            </div>
            {{-- CKEDITOR and Mail->getBodyText need pre tag , but still save html tags and show all html in show.blade--}}
            {{-- {{Form::textarea('content', '<pre>'.$ticket->content.'</pre>', ['id' => 'article-ckeditor', 'class' => 'form-control', 'placeholder' => 'Content'])}} --}}
            {{Form::textarea('content', null, ['class' => 'form-control', 'placeholder' => 'Ticket Description'])}}
        </div>
        <div class="form-group">
            {{Form::file('attachment')}}
        </div>
    {!! Form::close() !!}
@endsection