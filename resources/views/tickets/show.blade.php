@extends('layouts.app')
@inject('ticket_controller','App\Http\Controllers\TicketsController')
@section('content')
    <h1>Ticket No. {{$ticket->id}} - {{$ticket->subject}}</h1>
    <hr>
    <a href="/tickets" class="btn btn-primary">Go Back</a>
    <a href="/tickets/{{$ticket->id}}/edit" class="btn btn-success">Edit</a>
    {!!Form::open(['action' => ['TicketsController@destroy', $ticket->id], 'method' => 'POST', 'class' => 'float-right'])!!}
        {{Form::hidden('_method', 'DELETE')}}
        {{Form::submit('Delete', ['class' => 'btn btn-danger' , 'onclick' => "return  confirm('Do you want to delete this ticket ?')"])}}
    {!!Form::close()!!} 
    <hr>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr><td><h5><strong>Status:      </strong></h5></td><td>{{$ticket->status->name}}  </td><td><h5><strong>Start Time:    </strong></h5></td><td>{{isset($ticket->created_at) ? $ticket->created_at->format('d.m.Y H:i'):''}}</td></tr>
            <tr><td><h5><strong>Assignee:    </strong></h5></td><td>{{isset($ticket->user->name) ? $ticket->user->name : null}}    </td><td><h5><strong>Reaction Time:  </strong></h5></td><td>{{isset($ticket->reacted_at) ? $ticket->reacted_at->format('d.m.Y H:i'):''}}</td></tr>
            <tr><td><h5><strong>Customer:    </strong></h5></td><td>{{$ticket->owner}}         </td><td><h5><strong>End Time:      </strong></h5></td><td>{{isset($ticket->closed_at)  ? $ticket->closed_at->format('d.m.Y H:i'):null}}</td></tr>
            <tr>
                <td><h5><strong>Customer Email: </strong></h5></td><td>{{$ticket->owner_mail}}</td>
                <td><h5><strong>CC Email:</strong></h5></td>
                <td>@foreach (explode(",", $ticket->cc) as $cc) <p>{{$cc}}</p> @endforeach </td>
            </tr>
        </table>
        @if(strpos($ticket->owner_mail, App\Sla::find('IBM')->domain))
            <hr>
            <table class="table table-bordered">
            <thead>
                <tr class="align-middle"><th style="text-align:center" colspan="4" class="align-middle"><h5>IBM SLA Detail</h5></th></tr>
                <tr class="align-middle">
                    <th style="text-align:center" colspan="2" class="align-middle"><h5>SLA</h5></th>
                    <th style="text-align:center" colspan="2" class="align-middle"><h5>No SLA</h5></th>
                </tr>
            </thead>
                <tr>
                    <td><h5><strong>IBM Locations:</strong></h5></td>
                    <td>
                        {{isset(App\Sla_detail::where('ticket_id',"=",$ticket->id)->first()->location_id) ? 
                        App\Location::find(App\Sla_detail::where('ticket_id',"=",$ticket->id)->first()->location_id)->name : null}}
                    </td>   
                    <td rowspan="4" colspan="2">
                        <ul>
                            @if(isset(App\Sla_detail::where('ticket_id','=', $ticket->id)->first()->no_sla_reason_id)) 
                                @foreach(explode(',',App\Sla_detail::where('ticket_id','=', $ticket->id)->get('no_sla_reason_id')) as $no_sla_reason_id)
                                    <li>{{App\No_sla_reason::find(preg_replace("/[^0-9]/","", $no_sla_reason_id))->name}}</li>
                                @endforeach
                            @endif
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td><h5><strong>Failure Class:</strong></h5></td>
                    <td>
                        {{isset(App\Sla_detail::where('ticket_id',"=",$ticket->id)->first()->failure_class_id) ? 
                                App\Sla_detail::where('ticket_id',"=",$ticket->id)->first()->failure_class_id : null}}
                    </td>
                </tr>
                <tr>
                    <td><h5><strong>Hardware:</strong></h5></td>
                    <td>
                        <ul>
                            @if(isset(App\Sla_detail::where('ticket_id','=', $ticket->id)->first()->hw_id)) 
                                @foreach(explode(',',App\Sla_detail::where('ticket_id','=', $ticket->id)->get('hw_id')) as $hw_id)
                                    <li>{{App\Hw::find(preg_replace("/[^0-9]/","", $hw_id))->name}}</li>
                                @endforeach
                            @endif
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td><h5><strong>Software:</strong></h5></td>
                    <td>
                        <ul>
                            @if(isset(App\Sla_detail::where('ticket_id','=', $ticket->id)->first()->sw_id)) 
                                @foreach(explode(',',App\Sla_detail::where('ticket_id','=', $ticket->id)->get('sw_id')) as $sw_id)
                                    <li>{{App\Sw::find(preg_replace("/[^0-9]/","", $sw_id))->name}}</li>
                                @endforeach
                            @endif
                        </ul>
                    </td>
                </tr>
            </table>
        @endif
    </div>
    <hr>
    <h2>Problem Description</h2>
    <div>
        <pre style="font-family:arial;font-size:12pt">
            {{$ticket->content}}
        </pre>
    </div>
    <hr>
    <div class="text-center">
        @include('tickets.index_spent_times')
    </div>
    @if(count($ticket->attachments) > 0)
        <h2>Attachments</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <th style="text-align:center;width:200px"><h5>File Owner</h5></th>
                    <th style="text-align:center;width:800px"><h5>File for Download</h5></th>
                </thead>
                @foreach($ticket->attachments->sortBy('created_at') as $attachment)
                    <tr>
                        <td>
                            <p>{{$attachment->owner}}</p>
                        </td>
                        <td>
                            <a href="{{ URL::to('/storage/attachments/'.$attachment->filename) }}">{{$attachment->filename}}</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif
    @include('posts.index')
@endsection