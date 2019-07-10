<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post;
use App\Ticket;
use App\User;

class PostsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return $posts = Post::where('title', 'Post Two')->get();
        // $posts = DB::select('SELECT * FROM posts');
        // $posts = Post::orderBy('created_at','desc')->take(1)->get();
        // $posts = Post::orderBy('created_at','desc')->get();
        $posts = Post::orderBy('created_at','desc')->paginate(10);
        return view('posts.index')->with('posts', $posts);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_ticket_log($ticket_id)
    {
        return view('posts.index_ticket_log')->with('ticket_id', $ticket_id);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_ticket_log_and_all_posts($ticket_id)
    {
        return view('posts.index_ticket_log_and_all_posts')->with('ticket_id', $ticket_id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Ticket $ticket)
    {
        return view('posts.create')->with('ticket',$ticket);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            // 'title' => 'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999' //image size < 2MB
        ]);

        // Handle File Upload
        if($request->hasFile('cover_image')){
            // Get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            // Filename to store
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $path = $request->file('cover_image')->storeAS('public/cover_images', $fileNameToStore);
        } else {
            $fileNameToStore = 'noimage.jpg';
        }

        // Create Post
        $post = new Post;
        $post->title = null !== $request->input('title') ? $request->input('title') : '';
        $post->body = $request->input('body');
        $post->user_id = auth()->user()->id;
        $post->cover_image = $fileNameToStore;
        $ticket_id = $request->input('ticket_id');
        $post->ticket_id = $ticket_id;
        $post->is_user_action = false;
        $is_sent_to_customer = false;
        if($request->input('is_sent_to_customer') === 'yes'){
            $is_sent_to_customer = true;
            $post->is_sent_to_customer = true;
        }
        $post->save();
        app('App\Http\Controllers\TicketsController')->send_notification_for_post( auth()->user()->name , auth()->user()->email , $post , $is_sent_to_customer );
        return redirect('/tickets/'.$ticket_id)->with('success','Post Created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        return view('posts.show')->with('post' , $post);
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);
        $ticket_id = $post->ticket->id;
        // Check for correct user
        if(auth()->user()->id != $post->user_id && !auth()->user()->is_admin){
            return redirect('/tickets/'.$ticket_id)->with('error','Unautorized Page');
        }
        return view('posts.edit')->with('post',$post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            // 'title' => 'required',
            'body' => 'required'
        ]);

        // Handle File Upload
        if($request->hasFile('cover_image')){
            // Get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            // Filename to store
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $path = $request->file('cover_image')->storeAS('public/cover_images', $fileNameToStore);
        } 

        // Update Post
        $post = Post::find($id);
        $old_post = $post->replicate();
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        if($request->hasFile('cover_image')){
            $post->cover_image = $fileNameToStore;
        }
        $post->save();
        $ticket_id = $post->ticket->id;
        $change_exists = false;
        $change_exists = false;
        $post_update_text = null;
        if($this->change_exists($old_post , $post , $post_update_text)) {
            app('App\Http\Controllers\TicketsController')->send_notification_for_post_updated($post, $post_update_text);
            return redirect('/tickets/'.$ticket_id)->with('success','Post Updated');
        } else {
            return redirect('/tickets/'.$ticket_id);
        }
    }

    private function change_exists($old_post , $new_post , &$post_update_text) {
        $change_exists = false;
        if( trim($old_post->title) !== trim($new_post->title) ) {
            $post_update_text = $post_update_text . "\r\n" . "POST TITTLE : " . strip_tags(nl2br("\r\n" . ' from '. "\r\n" . $old_post->title . "\r\n" . ' to ' . "\r\n" . $new_post->title));
            $post_update_text = $post_update_text . "\r\n" . '_________________________________________'."\r\n";
            $change_exists = true;
        } 
        if( trim($old_post->body) !== trim($new_post->body) ) {
            $post_update_text = $post_update_text . "\r\n" . "POST BODY : " . strip_tags(nl2br("\r\n" . ' from '. "\r\n" . $old_post->body . "\r\n" . ' to ' . "\r\n" . $new_post->body));
            $post_update_text = $post_update_text . "\r\n" . '_________________________________________'."\r\n";
            $change_exists = true;
        } 
        return $change_exists;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        $ticket_id = $post->ticket->id;

        // Check for correct user
        if(!auth()->user()->is_admin) {
            if(auth()->user()->id != $post->user_id){
                return redirect('/tickets/'.$ticket_id)->with('error','Unautorized Page');
            }
        }
        if($post->cover_image != 'noimage.jpg'){
            // Delete Image
            Storage::delete('public/cover_images/'.$post->cover_image);
        }
        $post->delete();    
        app('App\Http\Controllers\TicketsController')->send_notification_for_post_deleted($post);
        return redirect('/tickets/'.$ticket_id)->with('success','Post Removed');
    }

    public function add($post) {
        $ticket = null;
        if(null == $post->ticket) {
            $ticket = $post->ticket_id;
        } else {
            $ticket = $post->ticket;
        }
        $this->add_post($post->body , $ticket->owner , $ticket->owner_mail , $ticket->id, $post->title, $post_is_user_action = true);
    }

    public function add_post($post_text, $sender, $sender_mail, $ticket_id, $post_title = null, $post_is_user_action = false) {
        $post = new Post(); 
        $ticket = Ticket::find($ticket_id);
        if($post_title == null){
            $post->title = 'Email of ' . $ticket->get_customer_company_name($sender_mail) . ' - ' . $sender ;
        } else {
            $post->title = $post_title;
        }
        $post->body = $post_text;
        $post->ticket_id = $ticket_id;
        $users = User::all();
        foreach ($users as $user){
            if ($user->email == $sender_mail) {
                $post->user_id = $user->id;
                break;
            }
            $post->user_id = 1;
        }
        $post->cover_image = 'noimage.jpg';
        $post->is_user_action = $post_is_user_action;
        
        if($post_title == null){    // post is created by user
            $this->remove_email_history($post);
            app('App\Http\Controllers\TicketsController')->send_notification_for_post($sender, $sender_mail, $post);
        } else {                    // post is created , when user update the ticket
            app('App\Http\Controllers\TicketsController')->send_notification_for_ticket_changed($sender, $sender_mail, $post);
        }
        $post->save();
    }
    
    public function remove_email_history($post) {
        $lenght = strlen($post->body);
        $delete_position = strpos($post->body , '"'. env('MAIL_USERNAME') .'" <'. env('MAIL_USERNAME') .'>');
        $delete_position = $delete_position === false ? $lenght - 1 : $delete_position;
        $mail_text = substr($post->body, 0, -1 * ($lenght - $delete_position));
        $mail_text = trim(str_replace('From:' ,'', $mail_text));
        $post->body = $mail_text;
    }
}
