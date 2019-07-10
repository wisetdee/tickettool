<div>
    <table class="table table-striped">
        <tr>
            <td>
                <small>
                    <strong>{{$post->user->name}}</strong> 
                    @if($post->update_at != null) 
                        {{$post->update_at->format('d.m.Y H:i')}} 
                    @else
                        {{$post->created_at->format('d.m.Y H:i')}}
                    @endif
                </small>
                <small class="float-right text-danger"><strong>{{$post->is_user_action ? 'ticket change log' : ''}}</strong></small>
                <br>
                <h3>{{$post->title}}</h3>
                @if($post->is_sent_to_customer)
                    <strong style="color:blue">{{App\User::find($post->user_id)->name}} &nbsp has sent this post &nbsp on &nbsp&nbsp {{$post->created_at($post->created_at)}} &nbsp&nbsp to &nbsp&nbsp {{$post->get_ticket_customers()}}</strong>
                    <br><br>
                @endif
                {!!Form::open(['action' => ['PostsController@destroy', $post->id], 'method' => 'POST'])!!}
                    {{-- @if(auth()->user()->id == $post->user_id || auth()->user()->is_admin == true)    --}}  {{-- only admin can see ticket change log --}}
                        @if($post->is_user_action == false || auth()->user()->is_admin == true)     {{-- users may not delete even their own ticket change log --}}
                            @if((!$post->is_sent_to_customer && $post->user->id === auth()->user()->id) || auth()->user()->is_admin ) 
                                <a href="{{env('APP_URL')}}/posts/{{$post->id}}/edit" class="btn btn-primary">Edit</a>
                                {{Form::hidden('_method', 'DELETE')}}
                                {{Form::submit('Delete', ['class' => 'btn btn-danger' , 'onclick' => "return  confirm('Do you want to delete this post ?')"])}}
                            @endif
                        @endif
                    {{-- @endif --}}
                    <pre><h5>{{$post->body}}</h5></pre>
                    @if($post->cover_image != 'noimage.jpg') 
                        <a href="/storage/cover_images/{{$post->cover_image}}">
                            <img style="width:10%" src="/storage/cover_images/{{$post->cover_image}}">
                        </a>
                    @endif
                {!!Form::close()!!}
            </td>
        </tr>
    </table>
</div>
<hr>