<?php

namespace App;

use App\Order;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotEnoughTicketsException;

class Concert extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['date'];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getSellPriceAttribute()
    {
        return '$' . number_format($this->ticket_price / 100, 2);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($quantity, $email)
    {
        $reservation = $this->reserveTickets($quantity);

        return Order::withReservation($email, $reservation);
    }

    public function reserveTickets($quantity)
    {
        $tickets = $this->findTickets($quantity);

        return Reservation::reserve($tickets);
    }

    public function findTickets($quantity)
    {
        $remainingTickets = $this->remainingTickets();
        if ($quantity > $remainingTickets->count()) {
            throw new NotEnoughTicketsException;
        }

        return $remainingTickets->take($quantity);        
    }

    public function addTickets($quantity)
    {
        collect(range(1, $quantity))->each(function () {
            $this->tickets()->save(new Ticket);
        });

        return $this;
    }

    public function remainingTickets()
    {
        return $this->tickets()->available()->get();
    }

    public function hasOrderFor($email)
    {
        return $this->orderFor($email) != null;
    }

    public function orderFor($email)
    {
        return $this->orders()->where('email', 'john@example.com')->first();
    }
}
