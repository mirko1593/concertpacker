<?php

namespace App\Http\Controllers;

use App\Order;
use App\Ticket;
use App\Concert;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;

class ConcertOrdersController extends Controller
{
    protected $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concert_id, Request $request)
    {
        $concert = Concert::findOrFail($concert_id);
        
        $concert->orderTickets($request['ticket_quantity'], $request['email']);

        $this->paymentGateway->charge($concert->ticket_price * $request['ticket_quantity'], $request['payment_token']);

        return response()->json([], 201);
    }
}
