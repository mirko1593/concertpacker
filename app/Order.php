<?php

namespace App;

use App\Ticket;
use App\Concert;
use Illuminate\Database\Eloquent\Model;
use App\Facades\OrderConfirmationNumber;
use App\OrderConfirmationNumberGenerator;

class Order extends Model
{
    protected $guarded = ['id'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount / 100, 2);
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(), 
            'amount' => $this->amount, 
            'confirmation_number' => $this->confirmation_number
        ];
    }

    public static function withReservationAndCharge($reservation, $charge)
    {
        $order = Order::create(array_merge($reservation->toArray(), [
            'confirmation_number' => self::generateNumber(), 
            'card_last_four' => $charge->cardLastFour()
        ]));

        $reservation->getTickets()->each(function ($ticket) use ($order) {
            $order->tickets()->save($ticket);
        });

        return $order;
    }

    public static function findByConfirmationNumber($confirmation_number)
    {
        return self::where('confirmation_number', $confirmation_number)->firstOrFail();
    }

    public static function generateNumber()
    {
        return app(OrderConfirmationNumberGenerator::class)->generate();
    }
}
