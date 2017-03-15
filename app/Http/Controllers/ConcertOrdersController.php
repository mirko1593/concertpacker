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
        $order = Order::create([
            'email' => $request['email'],
            'concert_id' => $concert_id
        ]);
        collect([
            new Ticket,
            new Ticket,
            new Ticket
        ])->each(function ($ticket) use ($order) {
            $order->tickets()->save($ticket);
        });

        $this->paymentGateway->charge($concert->ticket_price * $request['ticket_quantity'], $request['payment_token']);
        $concert->orders()->save($order);

        return response()->json([], 201);
    }
}
