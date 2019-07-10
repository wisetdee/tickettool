<?php
     
namespace App\Http\Controllers;
     
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;

class TicketDatatableController extends Controller
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
    public function show_datatable(Request $request)
    {
        if ($request->ajax()) {
            // $data = Ticket::latest()->get();         // does not work for multiple same colume name when use select join
            // $data = View_ticket::latest()->get();    // does not work
            
            $data = Ticket::join('status'       , 'tickets.status_id'   , '=', 'status.id')
                          ->join('users'        , 'tickets.user_id'     , '=', 'users.id')
                          ->leftjoin('sla_detail'   , 'tickets.id'          , '=', 'sla_detail.ticket_id')
                        //   ->leftjoin('sla_detail'   , function($join) // for multiple join condition
                        //     {
                        //         $join->on( 'tickets.id'   , '=', 'sla_detail.ticket_id');
                        //         // $join->on( ... next join condition ...)
                        //     })
                ->select([
                            'tickets.id'
                            ,'tickets.subject'
                            , DB::raw('status.name AS status_name')
                            , DB::raw('users.name AS user_name')
                            // TODO : Show failure class on datatable, these lines below does not work
                            , DB::raw('sla_detail.failure_class_id AS failure_class')
                            // , 'sla_detail.failure_class_id' 
                            // , DB::raw('ISNULL( sla_detail.failure_class_id , 0) AS failure_class')
                            // , DB::raw('ISNULL( (SELECT TOP 1 failure_class_id AS fcl FROM sla_detail WHERE fcl.ticket_id = tickets.id) , 0) AS failure_class')
                            ,'tickets.owner'
                            ,'tickets.sla'
                        ]);            
            
            return Datatables::of($data)
                    // ->addIndexColumn()
                    ->addColumn('action', function($ticket){
                        $btn = '<a href="'.env('APP_URL').'/tickets/'.$ticket->id.'" class="edit btn btn-primary btn-sm">View</a>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    // ->setRowClass(function ($ticket) {   // TODO : make 2 row colors different for odd and even lines
                    //     return $ticket->id % 2 == 0 ? 'alert-success' : 'alert-warning'; 
                    // })
                    ->make(true);
        }
        return view('tickets.datatable');
    }
}