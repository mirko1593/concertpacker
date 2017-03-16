<?php

namespace App;

use App\Ticket;
use App\Concert;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // public function concert()
    // {
    //     return $this->belongsTo(Concert::class);
    // }

    public function cancel()
    {
        $this->tickets->each(function ($ticket) {
            $ticket->release();
        });
        $this->delete();
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
            'amount' => $this->amount
        ];
    }

    public static function withTickets($email, $tickets)
    {
        $order = Order::create([
            'email' => $email, 
            'amount' => $tickets->sum('price')
        ]);

        $tickets->each(function ($ticket) use ($order) {
            $order->tickets()->save($ticket);
        });

        return $order;
    }
}
