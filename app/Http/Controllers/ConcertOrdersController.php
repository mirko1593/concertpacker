<?php

namespace App\Http\Controllers;

use App\Order;
use App\Ticket;
use App\Concert;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;

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
            $reservation = $concert->reserveTickets($request['ticket_quantity']);

            $this->paymentGateway->charge($reservation->totalCost(), $request['payment_token']);

            $order = Order::withReservation($request['email'], $reservation);
        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }

        return response()->json($order, 201);
    }
}
