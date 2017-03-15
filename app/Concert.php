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
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($quantity, $email)
    {
        $remainingTickets = $this->remainingTickets();
        if ($quantity > $remainingTickets->count()) {
            throw new NotEnoughTicketsException;
        }
        $order = Order::create([
            'email' => $email,
            'concert_id' => $this->id
        ]);

        $remainingTickets->take($quantity)->each(function ($ticket) use ($order) {
            $order->tickets()->save($ticket);
        });

        $this->orders()->save($order);
    }

    public function cancelTickets($email)
    {
        $order = $this->orders()->where('email', $email)->first();
        $order->cancel();
    }

    public function addTickets($quantity)
    {
        collect(range(1, $quantity))->each(function () {
            $this->tickets()->save(new Ticket);
        });
    }

    public function remainingTickets()
    {
        return $this->tickets()->available()->get();
    }
}
