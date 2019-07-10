@extends('layouts.app')

@section('content')
    <div class="jumbotron text-center">
        <h1>{{$title}}</h1>
        <p>This is the Ticketing tool for IBM support, our last customer</p>
        {{-- <p><a class="btn btn-primary btn-lg" href="/login" role="button">Login</a>
           <a class="btn btn-success btn-lg" href="/register" role="button">Register</a>
        </p> --}}
        <div class="flex-center position-ref full-height">

            {{-- <form action="{{ route('sendmail') }}" method="POST">
                <input type="email" name="mail" placeholder="mail address">
                <input type="text" name="title" placeholder="title">
                <button type="submit">Send me a mail</button>
                {{ csrf_field() }}
            </form> --}}

            {{-- {!! Form::open(['action' => ['MailController@send'], 'method' => 'POST']) !!}
                <div class="form-group">
                    {{Form::text('mail', '', ['class' => 'form-control', 'placeholder' => 'mail address'])}}
                </div>
                <div class="form-group">
                    {{Form::text('title', '', ['class' => 'form-control', 'placeholder' => 'title'])}}
                </div>
                {{Form::submit('Submit', ['class' => "btn btn-primary"])}}
            {!! Form::close() !!} --}}

        </div>
    </div>
@endsection