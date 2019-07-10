@extends('layouts.app')

@section('content')
{!! Form::open(['action' => ['TicketsController@update_user'], 'method' => 'GET', 'enctype' => 'multipart/form-data']) !!}
    <h1>PLEASE, ASSIGNE THE TICKET TO A SUPPORTER FIRST</h1>
    <h2>Ticket #{{$ticket->id}} - {{$ticket->subject}}</h2>
    <hr>
    {{Form::submit('Save', ['class' => "btn btn-success"])}}
    <a href="/tickets/{{$ticket->id}}" class="btn btn-secondary">cancel</a>
    <hr>
    <h5><strong>Assignee:</strong></h5>{!! Form::select('user_id', App\User::where('id','>',1)->pluck('name', 'id'), $ticket->user_id, ['class' => 'form-control']) !!}
    {{ Form::hidden('ticket_id', $ticket->id) }}
{!! Form::close() !!}
@endsection