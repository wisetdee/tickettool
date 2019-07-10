@extends('layouts.app')

@section('content')
<h1>Ticket History + all Posts</h1>
<h2>#{{$ticket_id}} - {{App\Ticket::find($ticket_id)->subject}}</h2>
<hr>
<a href="{{url('tickets')}}/{{$ticket_id}}" class="btn btn-primary">Go Back</a>
<hr>
@if(count(App\Ticket::find($ticket_id)->posts) > 0 )
    @foreach(App\Post::where([['ticket_id','=',$ticket_id]])->orderBy('created_at')->get() as $post)
        @include('posts.index_body')
    @endforeach
@else
    <p>No change found for this ticket</p>
@endif

@endsection
