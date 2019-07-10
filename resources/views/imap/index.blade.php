{{-- for TEST to be removed --}}

@extends('layouts.app')
@section('content')
<pre>
    <h1>{{$subject}}</h1>
    <ul class="list-group">
        <li class="list-group-item">{{$personal}}</li>
        <li class="list-group-item"><a href="mailto:{{$mail}}">{{$mail}}</a></li>
        <li class="list-group-item">{{$content}}</li>
        @if(isset($attachments) && count($attachments) > 0)
            <ul class="list-group">
                @foreach($attachments as $file)
                    <li class="list-group-item">{{$file}}</li>
                @endforeach
            </ul>
        @endif
    </ul>
</pre>
@endsection