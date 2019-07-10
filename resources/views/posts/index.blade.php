<hr>
<h2>Posts</h2>
@if(auth()->user()->is_admin || auth()->user()->is_spoc)
    <a href="{!! route('index_ticket_log' , ['ticket_id' => $ticket->id]) !!}" class="btn btn-secondary">Show Ticket History</a>
    <a href="{!! route('index_ticket_log_and_all_posts' , ['ticket_id' => $ticket->id]) !!}" class="btn btn-dark">Show Ticket History And All Posts</a>
    <br><br>
@endif

@if(count($ticket->posts) > 0 )
    @foreach(App\Post::where([['ticket_id','=',$ticket->id],['is_user_action','=',false]])->orderBy('created_at')->get() as $post)
        @include('posts.index_body')
    @endforeach
@else
    <h5>You have no posts</h5>
@endif
@include('posts.create')