<h1>Check Ticket Overdue</h1>
@foreach ($details_array as $details)
    {{$details['subject']}} <hr>
    {{$details['links']}} <hr>
    {{$details['title']}} <hr>
    {{$details['body']}} <hr>
    <hr>
    <hr>
@endforeach