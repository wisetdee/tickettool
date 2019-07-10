@extends('layouts.app')

@section('content')
    <h1>Edit Ticket {{$ticket->id}}</h1>
    {!! Form::open(['action' => ['TicketsController@update', $ticket->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <hr>
        {{Form::submit('Save', ['class' => "btn btn-success"])}}
        {{Form::hidden('ticket_id',$ticket->id)}}
        {{Form::hidden('_method','PUT')}}
        @if(       !isset($ticket->sla) 
                || (isset($ticket->sla) && isset(App\Sla_detail::where('ticket_id','=',$ticket->id)->first()->failure_class_id))
                )
            <a href="/tickets/{{$ticket->id}}" class="btn btn-secondary">cancel</a>
        @endif
        <hr>
        <div class="form-group">
            <br>
            {{Form::text('subject', $ticket->subject, ['class' => 'form-control', 'placeholder' => 'Subject'])}}
            <br>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <th style="width:20%"><h5><strong>Status:         </strong></h5></th>
                        <th style="width:20%">{!! Form::select('status_id', App\Status::pluck('name', 'id'), $ticket->status_id, ['class' => 'form-control']) !!}  </th>
                        <th style="width:20%"><h5><strong>Start Time:     </strong></h5></th>
                        <th style="width:40%">{{  Form::input('dateTime-local', 'created_at', isset($ticket->created_at) ? date('Y-m-d\TH:i',  strtotime($ticket->created_at)) : $ticket->created_at, ['class' => 'form-control']) }} </th>
                    </thead>
                    <tr>
                        <td><h5><strong>Assignee:       </strong></h5></td>
                        <td>{!! Form::select('user_id', App\User::where('id','>',1)->pluck('name', 'id'), $ticket->user_id, ['class' => 'form-control']) !!}</td>
                        <td><h5><strong>Reaction Time:  </strong></h5></td>
                        <td>{{  Form::input('dateTime-local', 'reacted_at', isset($ticket->reacted_at) ? date('Y-m-d\TH:i',  strtotime($ticket->reacted_at)) : $ticket->reacted_at, ['class' => 'form-control'])}}</td>
                    </tr>
                    <tr>
                        <td><h5><strong>Customer:       </strong></h5></td>
                        <td>{{  Form::text('owner', $ticket->owner, ['class' => 'form-control', 'placeholder' => 'Owner'])}}</td>
                        <td><h5><strong>End Time:       </strong></h5></td>
                        <td>{{  Form::input('dateTime-local', 'closed_at' , isset($ticket->closed_at)  ? date('Y-m-d\TH:i',  strtotime($ticket->closed_at))  : $ticket->closed_at , ['class' => 'form-control'])}}</td>
                    </tr>
                    <tr>
                        <td><h5><strong>Customer Email: </strong></h5></td>
                        <td>{{  Form::text('owner_mail', $ticket->owner_mail   , ['class' => 'form-control', 'placeholder' => 'Owner Email Address'])}}</td>
                        <td><h5><strong>CC Email:       </strong></h5></td>
                        <td>{{  Form::textarea('cc_mail', str_replace(',' , ' , ', $ticket->cc) , ['class' => 'form-control', 'placeholder' => 'CC Email Address'])}}</td>
                    </tr>
                    <tr>
                        <td><h5><strong>Has IBM SLA Detail:</strong></h5></td>
                        {{-- <td>
                            <p> {{ Form::radio('sla_yes_no', 1,  isset($ticket->sla) ? true : false) }} Yes</p>
                            <p> {{ Form::radio('sla_yes_no', 0, !isset($ticket->sla) ? true : false) }} No  </p>
                        </td> --}}
                        @if(isset($ticket->sla))
                            <td style="width:10%" class="text-left"><label class="switch"><input type="checkbox" name="sla_yes_no" value="yes" checked><span class="slider round"></span></label></td>
                        @else
                            <td style="width:10%" class="text-left"><label class="switch"><input type="checkbox" name="sla_yes_no" value="yes" ><span class="slider round"></span></label></td>
                        @endif
                    </tr>
                </table>
                @if(strpos($ticket->owner_mail, App\Sla::find('IBM')->domain))
                    <hr>
                    <table id="sla_panel" class="table table-bordered">
                        <thead>
                            <tr class="align-middle"><th style="text-align:center" colspan="4" class="align-middle"><h5>IBM SLA Detail</h5></th></tr>
                            <tr class="align-middle">
                                <th style="text-align:center" colspan="2" class="align-middle"><h5>SLA</h5></th>
                                <th style="text-align:center" colspan="2" class="align-middle"><h5>No SLA</h5></th>
                            </tr>
                        </thead>
                        <tr>
                            <td><h5><strong>IBM Locations:</strong></h5></td>
                            <td>{!! Form::select('location'       , App\Location::pluck('name', 'id')      , isset(App\Sla_detail::where('ticket_id',$ticket->id)->first()->location_id)      ? App\Sla_detail::where('ticket_id',$ticket->id)->first()->location_id      : null, ['class' => 'form-control']) !!}</td>   
                            <td rowspan="4" colspan="2">
                                {!! Form::select('no_sla_reason[]', App\No_sla_reason::pluck('name', 'id') , isset(App\Sla_detail::find($ticket->id)->no_sla_reason_id) ? explode(',',App\Sla_detail::find($ticket->id)->no_sla_reason_id) : '' , ['multiple'=>'multiple' , 'class' => 'form-control']) !!}
                            </td>
                        </tr>
                        <tr>
                            <td><h5><strong>Failure Class:</strong></h5></td>
                            <td>{!! Form::select('failure_class', App\Failure_class::pluck('id', 'id')  , isset(App\Sla_detail::where('ticket_id',$ticket->id)->first()->failure_class_id) ? App\Sla_detail::where('ticket_id',$ticket->id)->first()->failure_class_id : null, ['class' => 'form-control']) !!}</td>   
                        </tr>
                        <tr>
                            <td><h5><strong>Hardware:</strong></h5></td>
                            <td>{!! Form::select('hw[]', App\Hw::pluck('name', 'id') , isset(App\Sla_detail::find($ticket->id)->hw_id) ? explode(',',App\Sla_detail::find($ticket->id)->hw_id) : '' , ['multiple'=>'multiple' , 'class' => 'form-control']) !!}</td>
                        </tr>
                        <tr>
                            <td><h5><strong>Software:</strong></h5></td>
                            <td>{!! Form::select('sw[]', App\Sw::pluck('name', 'id') , isset(App\Sla_detail::find($ticket->id)->sw_id) ? explode(',',App\Sla_detail::find($ticket->id)->sw_id) : '' , ['multiple'=>'multiple' , 'class' => 'form-control']) !!}</td>
                        </tr>
                    </table>
                @endif
            </div>
            <hr>
            <h2>Problem Description</h2>
            {{-- CKEDITOR and Mail->getBodyText need pre tag , but still save html tags and show all html in show.blade--}}
            {{-- {{Form::textarea('content', '<pre>'.$ticket->content.'</pre>', ['id' => 'article-ckeditor', 'class' => 'form-control', 'placeholder' => 'Content'])}} --}}
            {{Form::textarea('content', $ticket->content, ['class' => 'form-control', 'placeholder' => 'Ticket Description'])}}
        </div>
        <div class="form-group">
            {{Form::file('attachment')}}
        </div>
    {!! Form::close() !!}
    @if(count($ticket->attachments) > 0)
        <h2>Attachments</h2>
        <ul class="list-group">
            <table>
                @foreach($ticket->attachments->sortBy('created_at') as $attachment)
                    <tr class="list-group-item">
                        <td style="width:250px">
                            <p>{{$attachment->owner}}</p>
                        </td>
                        <td style="width:800px">
                            <a href="{{ URL::to('/storage/attachments/'.$attachment->filename) }}">{{$attachment->filename}}</a>
                        </td>
                        <td>
                            @if(strpos($attachment->filename , '.eml'))
                                <strong style="color:brown">Email</strong>                             
                            @endif
                            @if(!strpos($attachment->filename , '.eml') || auth()->user()->is_admin)
                                {!!Form::open(['action' => ['AttachmentsController@destroy', $attachment->id], 'method' => 'POST', 'class' => 'float-right'])!!}
                                    {{Form::hidden('_method', 'DELETE')}}
                                    {{Form::submit('Delete', ['class' => 'btn btn-danger' , 'onclick' => "return  confirm('Do you want to delete this Attachment $attachment->filename ?')"])}}
                                {!!Form::close()!!} 
                            @endif   
                        </td>
                    </tr>
                @endforeach
            </table>
        </ul>
    @endif
@endsection