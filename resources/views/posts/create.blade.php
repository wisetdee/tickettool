
<hr>
{!! Form::open(['action' => 'PostsController@store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <td style="width:10%" class="text-center">{{Form::submit('Create New Post', ['class' => "btn btn-success"])}}</td>
                <td style="width:10%" class="text-center"><label class="switch"><input type="checkbox" name="is_sent_to_customer" value="yes"><span class="slider round"></span></label></td>
                <td style="width:80%" class="text-left middle"><label class="h3 text-center" for="notify_customer">Send this post to the customer</label></td>
            </tr>
            <br><br>
            <tr><td colspan="3"><div class="form-group">{{Form::text('title', '', ['class' => 'form-control', 'placeholder' => 'Post Title'])}}</div></td></tr>
            <tr><td colspan="3"><div class="form-group">{{Form::textarea('body', '', ['id' => 'article-ckeditor', 'class' => 'form-control', 'placeholder' => 'Post Body'])}}</div></td></tr>
            <tr>
                <td colspan="3">
                    <div class="form-group">
                        <h4><label for="coverimage">Upload Image for Your Post (screenshot or photo)</label></h4>
                        {{Form::file('cover_image')}}
                    </div>
                </td>
            </tr>
        </table>
    </div>    
    {{ Form::hidden('ticket_id', $ticket->id) }}
{!! Form::close() !!}
<hr>
