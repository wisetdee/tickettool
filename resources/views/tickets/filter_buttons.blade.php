<a href="/tickets/create"                 class="btn btn-primary">Create Ticket</a>
<a href="/tickets"                        class="btn btn-success">All OPEN Tickets</a>
<a href="{{action("TicketsController@index_sla")}}"         class="btn btn-warning">IBM SLA OPEN tickets</a>
<a href="{{action("TicketsController@index_sla_closed")}}"  class="btn btn-secondary">IBM SLA CLOSED tickets</a>
<a href="{{action("TicketsController@index_closed")}}"      class="btn btn-dark">All CLOSED tickets</a>
<a href="/ticket_datatable"               class="btn btn-default" style="background-color:lightblue">SEARCH Tickets</a>