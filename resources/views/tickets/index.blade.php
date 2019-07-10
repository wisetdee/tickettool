@extends('layouts.app')

@section('content')

    <h1 class="bg-{{$class}} text-center text-{{$text_color}}">{{$title}}</h1>
    @include('tickets.filter_buttons')
    <hr>
    @include('tickets.index_body')    

@endsection
