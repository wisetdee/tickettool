<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('tickets.index');
});

// Route::get('/hello', function () {
//     // return view('welcome');
//     return '<h1>Hello World</h1>';
// });

// Route::get('/users/{id}/{name}',function($id, $name){
//     return 'This is user '.$name.' with and id'.$id;
// });

// Route::get('/about',function(){
//     return view('pages.about');
// });

Route::get('/', 'PagesController@index');
Route::get('/home', 'PagesController@index');
Route::get('/about', 'PagesController@about');
Route::get('/services', 'PagesController@services');
Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
Route::get('/user/activation/{token}', 'Auth\RegisterController@userActivation');
Route::get('/admin', 'TicketsController@admin');
Route::get('/ticket_config', 'TicketsController@ticket_config');    //for test, TODO : remove
Route::get('/tickets_sla', 'TicketsController@index_sla');
Route::get('/tickets_sla_closed', 'TicketsController@index_sla_closed');
Route::get('/tickets_closed', 'TicketsController@index_closed');
Route::get('/ticket_datatable', [
    'uses'=>'TicketDatatableController@show_datatable', 
    'as'=>'tickets.ticket_datatable'
]);
Route::get('/update_user', 'TicketsController@update_user');
Route::get('index_ticket_log/{ticket_id}', [
    'uses'  => 'PostsController@index_ticket_log',
    'as'    => 'index_ticket_log'
]);
Route::get('index_ticket_log_and_all_posts/{ticket_id}', [
    'uses'  => 'PostsController@index_ticket_log_and_all_posts',
    'as'    => 'index_ticket_log_and_all_posts'
]);

Route::get('/imap', 'ImapController@fetch_mail');
Route::get('/check_overdue', 'TicketsController@send_notification_for_overdue');
Route::get('/update_customer_spam_filter', 'UsersController@update_customer_spam_filter');
Route::get('/users/{user_id}', 'UsersController@index');
Route::resource('users', 'UsersController');
Route::resource('failure_class', 'Failure_classController');
Route::resource('posts', 'PostsController');
Route::resource('tickets', 'TicketsController');
Route::resource('spent_times', 'SpentTimesController');
Route::resource('attachments', 'AttachmentsController');
Route::resource('changepassword','PasswordController');  //don't ever user Route::resource('password'... because it already used by Laravel Auth
Auth::routes();

// only for TEST will be deleted
// Route::get('/datatable','DatatablesController@getIndex');
// Route::get('/anyData','DatatablesController@anyData')->name('datatables.data');
// // Alternative for route datatable
// Route::post('datatables/data', 'DatatablesController@anyData')->name('datatables.data');
// Route::get('datatables/', 'DatatablesController@getIndex')->name('datatables');

// TEST



// Route::post('sendmail', 'MailController@send');

// Route::post('/sendmail', function (\Illuminate\Http\Request $request, \Illuminate\Mail\Mailer $mailer){
//     $mailer
//         ->to($request->input('mail'))
//         ->send(new \App\Mail\MyMail($request->input('title')));
//     return redirect()->back();
// })->name('sendmail');
