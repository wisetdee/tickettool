@inject('ticket_controller','App\Http\Controllers\TicketsController')
@if(count($tickets) > 0)
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <th class="text-center"><h4><strong>Ticket</strong></h4></th>
                <th class="text-center"><h4><strong>Status</strong></h4></th>
                <th class="text-center"><h4><strong>Failure Class</strong></h4></th>
                <th class="text-center"><h4><strong>Assignee</strong></h4></th>
                <th class="text-center"><h4><strong>Customer</strong></h4></th>
                <th class="text-center"><h4><strong>SLA</strong></h4></th>
            </thead>
            @foreach($tickets as $ticket)
                <tr @if($ticket->status->name !== 'CLOSED')
                        style="{{$ticket->get_urgent_mark_for_sla_solution_overdue()['color']}}" 
                    @endif
                >
                    <td class="col-md-1">
                        <small>
                            <strong>{{$ticket->owner}}</strong> {{$ticket->created_at->format('d.m.Y H:i')}} 
                        </small>
                        <h4>
                            <a href="/tickets/{{$ticket->id}}">#{{$ticket->id}} - {{$ticket->subject}}</a>
                        </h4>
                        @if($ticket->status->name !== 'CLOSED')
                            <div><strong style="color:red;float:left">{{$ticket->get_urgent_mark_for_sla_solution_overdue()['text']}}</strong></div>
                        @endif
                    </td>
                    <td class="col-md-1 text-center">{{App\Status::where('id',$ticket->status_id)->first()->name}}</td>
                    <td class="col-md-1 text-center">{{$ticket_controller::get_failure_class($ticket)}}</td>
                    <td class="col-md-1 text-center">{{isset($ticket->user) ? $ticket->user->name : ''}}</td>
                    <td class="col-md-1 text-center">{{$ticket->get_customer_company_name()}}</td>
                    <td class="col-md-1 text-center">{{$ticket_controller::get_sla_yes_no($ticket)}}</td>
                    <td>
                        {!!Form::open(['action' => ['TicketsController@destroy', $ticket->id], 'method' => 'POST', 'class' => 'float-right'])!!}
                            {{Form::hidden('_method', 'DELETE')}}
                            {{Form::submit('Delete', ['class' => 'btn btn-danger' , 'onclick' => "return  confirm('Do you want to delete this ticket $ticket->id ?')"])}}
                        {!!Form::close()!!}
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    {{$tickets->links()}}
@else
    <p>No ticket found</p>
@endif