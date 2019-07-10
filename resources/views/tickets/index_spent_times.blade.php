<hr>
{{Form::label('spent_time_total','Total Spent time '.round($ticket_controller::get_spent_time($ticket),2).' hour(s)', ['class' => 'h4'])}}
<div class="form-group md-2">
    {!! Form::open(['action' => ['SpentTimesController@store'], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 15%">{{Form::label('spent_time_hour', 'Hour(s)', ['class' => 'h5 font-weight-bold text-center'])}}</th>
                        <th style="width: 70%">{{Form::label('spent_time_comment', 'Comment', ['class' => 'h5 font-weight-bold text-center'])}}</th>
                        <th style="width: 15%">
                            {{Form::label('new_spent_time_hour', 'Time Record', ['class' => 'h5 font-weight-bold text-center'])}}
                        </th>
                    </tr>
                </thead>
                <tr>
                    <td>
                        {{Form::number('spent_time_hour', '', ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Spent Hour(s)'])}}
                    </td>
                    <td>
                        {{Form::text('spent_time_comment', '', ['id' => 'article-ckeditor', 'class' => 'form-control', 'placeholder' => 'Comment for your spent time'])}}
                    </td>
                    <td class="text-center">
                        {{Form::submit('Save', ['class' => "btn btn-success"])}}
                    </td>
                </tr>
            </table>
        </div>
        {{ Form::hidden('ticket_id', $ticket->id) }}
        {{ csrf_field() }}
    {!! Form::close() !!}
</div>
@if(count($ticket->spent_times) > 0 )
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width: 15%">Hour(s)</th>
                    <th style="width: 15%">User</th>
                    <th style="width: 55%">comment</th>
                    <th style="width: 15%"></th>
                </tr>
            </thead>
            @foreach($ticket->spent_times->sortBy('created_at') as $spent_time)
                <tr>
                    <td>{{round($spent_time->hour,2)}}</td>
                    <td>
                        <small>
                            <strong>{{$spent_time->user->name}}</strong> 
                            @if($spent_time->update_at != null) 
                                {{$spent_time->update_at->format('d.m.Y H:i')}} 
                            @else
                                {{$spent_time->created_at->format('d.m.Y H:i')}}
                            @endif
                        </small>
                    </td>
                    <td>
                        <p>{{isset($spent_time->comment) ? $spent_time->comment : 'No Comment'}}</p>
                    <td>
                        {!!Form::open(['action' => ['SpentTimesController@destroy', $spent_time->id], 'method' => 'POST'])!!}
                            @if(auth()->user()->id == $spent_time->user_id || auth()->user()->is_admin == true)
                                {{Form::hidden('_method', 'DELETE')}}
                                {{Form::submit('Delete', ['class' => 'btn btn-danger' , 'onclick' => "return  confirm('Do you want to delete this time record ?')"])}}
                            @endif
                        {!!Form::close()!!}
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@else
    <p>There is no time record on this ticket.</p>
@endif    
<hr>