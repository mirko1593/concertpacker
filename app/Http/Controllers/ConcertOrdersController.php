<?php

namespace App\Http\Controllers;

use Exception;
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
        $this->validate($request, [
            'email' => 'required | email',
            'ticket_quantity' => 'required | integer | min:1',
            'payment_token' => 'required'
        ]);

        $concert = Concert::published()->findOrFail($concert_id);

        try {
            $this->paymentGateway->charge(
                $concert->ticket_price * $request['ticket_quantity'], 
                $request['payment_token']
            );
            $concert->orderTickets($request['ticket_quantity'], $request['email']);
        } catch (Exception $e) {
            return response()->json([], 422);
        }

        return response()->json([], 201);
    }
}
