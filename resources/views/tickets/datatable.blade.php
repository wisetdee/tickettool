{{-- layouts.app does not work with <head> of laravel datatable--}}
{{-- @extends('layouts.app') --}} 
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
</head>
<body>

<h1 class="bg-default text-center" style="background-color:lightblue">Search ticket</h1>
<div class="container">
    @include('tickets.filter_buttons')
    <hr>
    <table class="table table-bordered data-table">
        <thead>
            <tr>
                <th>Ticket No.</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Failure Class</th>
                <th>Assignee</th>
                <th>Customer</th>
                <th>SLA</th>
                <th width="100px">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
</body>
   
<script>
    $(function () {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tickets.ticket_datatable') }}",
            columns: [
                // {data: 'DT_RowIndex', name: 'Ticket ID'}, //BUG : id is not sortable
                {data: 'id'     , name: 'tickets.id'},
                {data: 'subject', name: 'tickets.subject'},
                {data: 'status_name' , name: 'status.name'},
                {data: 'failure_class', name: 'sla_detail.failure_class_id'},    // TODO : get data from table failure_class
                {data: 'user_name', name: 'users.name'},
                {data: 'owner'  , name: 'tickets.owner'},
                {data: 'sla'    , name: 'tickets.sla'},
                {data: 'action' , name: 'action', orderable: false, searchable: false},
            ]
        });
    });
</script>
<style>
    /* 2 alternate row coler ==> TODO : put this css in public css and anchor to layouts app.blade.php */
    table.data-table tr.odd { background-color: lightyellow; }
    table.data-table tr.even{ background-color: lightcyan; }
</style>
</html>