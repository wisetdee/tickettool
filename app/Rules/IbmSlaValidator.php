<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Sla;
use App\Status;
use App\Ticket;
use Carbon\Carbon;

class IbmSlaValidator implements Rule
{
    private $request, $owner_mail, $cc, $fault_email = null, $has_email_fault=false, $is_sla, $domain, 
            $created_at, $reacted_at, $closed_at, $is_closed,
            $has_sla_reason_conflict = false,
            $sla_unclear = false;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $owner_mail
     * @return bool
     */
    public function passes($attribute, $owner_mail)
    {
        // check Email
        $ticket = Ticket::find($this->request->input('ticket_id'));
        if(is_null($ticket)){
            $ticket = new Ticket();
        }
        $this->has_email_fault = $ticket->validate_email($this->request , $this->fault_email);
        // dd($this->has_email_fault);
        // check SLA reason coflict   
        if(null !== $this->request->input('ticket_id')) {
            if (
                         null !== $this->request->input('no_sla_reason')   // has no_sla_reason
                    && ( null !== $this->request->input('hw') || null !== $this->request->input('sw') ) // has no controller hardware or software failure
                    // && $this->request->input('sla_yes_no') == 'yes'   
                )
            {
                $this->has_sla_reason_conflict = true;
            }

            // check if there is no reason selected = SLA UNCLEAR
            if (
                    null == $this->request->input('no_sla_reason')// has no_sla_reason
                    && null == $this->request->input('hw')           // has no controller hardware failure
                    && null == $this->request->input('sw')           // has no controller software failure
                    && $this->request->input('sla_yes_no') == 'yes'  // IBM Ticket
                )
            {
                $this->sla_unclear = true;
            }
        }
        // prepare for message out put
        $this->owner_mail = $this->request->input('owner_mail');
        $this->domain = substr($this->owner_mail, strpos($this->owner_mail, '@') + 1);
        $this->is_sla = $this->request->input('sla_yes_no') == 'yes' ? true : false;
        
        // prepare Carbon datetimes
        $ticket = new Ticket();
        $this->created_at = $ticket->created_at($this->request);
        $this->created_at = null == $this->created_at ? Carbon::now() : $this->created_at;
        $this->reacted_at = $ticket->reacted_at($this->request);
        $this->is_closed = $this->request->input('status_id') == Status::where('name','CLOSED')->first()->id ? true : false; 
        $this->closed_at = $ticket->closed_at($this->request);
        if($this->closed_at == null) {
            $this->closed_at = Carbon::now();
        }

        // _____________message_output___________________________
        if($this->has_email_fault) {
            return false;
        }
        if($this->is_sla){
            if( $this->has_sla_reason_conflict ) {
                return !$this->has_sla_reason_conflict;
            }
            if(    $this->sla_unclear 
                && $this->request->input('status_id') == Status::where('name','CLOSED')->first()->id) { //when user want to close an IBM ticket, but  SLA is still UNCLEAR
                return !$this->sla_unclear;
            }
            if ($this->created_at->gt($this->reacted_at)) {
                return false;
            }
            if ($this->reacted_at->gt($this->closed_at)) {
                return false;
            }
            if ( null !== Ticket::find($this->request->input('ticket_id' ))) {  //null = date from create.blade , not_null = data from edit.blade
                $existing_ticket = Ticket::find($this->request->input('ticket_id'));
                if ($this->is_closed) {
                    if ($this->created_at->gt($this->closed_at)) {
                        return false;
                    }
                    if ($this->created_at->gt($this->reacted_at)) {
                        return false;
                    }
                }
            }
            return $this->is_sla && $this->domain === Sla::find('IBM')->domain;
        } else {
            if($this->domain === Sla::find('IBM')->domain) {    //if the customer is IBM , then the ticket must show SLA details
                return false;
            }
            return !$this->is_sla; // data validation OK => return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // _____________message_output___________________________
        if($this->has_email_fault) {
            return 'There is something wrong with this email addresse : ' . $this->fault_email;
        }
        if($this->is_sla){
            if( $this->has_sla_reason_conflict ) {
                return 'You have to select either SLA reason(s) or No SLA reason(s)'."\r\n".
                        'Because, an IBM ticket can only have SLA or No SLA.'."\r\n".
                        'If you need to record SLA time and No SLA time for same ticket,' ."\r\n". 
                        'then you can create other new ticket and record your time separatly.';
            }
            if( $this->sla_unclear 
                && $this->request->input('status_id') == Status::where('name','CLOSED')->first()->id) { //when user want to close an IBM ticket, but  SLA is still UNCLEAR
                return "Please, select at least one reason for an SLA or No SLA ticket before you close this IBM ticket";
            }
            if ($this->created_at->gt($this->reacted_at)) {
                return 'Start Time must be less than or equals Reaction Time';
            }
            if ($this->reacted_at->gt($this->closed_at)) {
                return 'Reaction Time must be less than equals Closed Time';
            }
            if ( null !== Ticket::find($this->request->input('ticket_id' ))) {  //null = date from create.blade , not_null = data from edit.blade
                $existing_ticket = Ticket::find($this->request->input('ticket_id'));
                if ($this->is_closed) {
                    if ($this->created_at->gt($this->closed_at)) {
                        return 'Start Time must be less than equals End Time   OR   End Time should not be greater than now';
                    }
                    if ($this->created_at->gt($this->reacted_at)) {
                        return 'Start Time must be less than equals Reaction Time   OR   Reaction Time should not be greater than now';
                    }
                }
            }
            return 'For IBM SLA , the customer email address domain must be @'.Sla::find('IBM')->domain;
        } else {
            if($this->domain === Sla::find('IBM')->domain) {
                return 'Please, select Yes to "Has IBM SLA", because the customer email contains @'.Sla::find('IBM')->domain;
            }
            return 'No failure found';
        }
        // return 'An unexpected failure occors. Please, tell Nibhond the reponsible programer !';
    }

}
