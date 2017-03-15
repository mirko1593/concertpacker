<?php

namespace App;

use App\Order;
use Illuminate\Database\Eloquent\Model;

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

    public function orderTickets($quantity, $email)
    {
        $order = Order::create([
            'email' => $email,
            'concert_id' => $this->id
        ]);
        collect(range(1, $quantity))->each(function () use ($order) {
            $order->tickets()->save(new Ticket);
        });

        $this->orders()->save($order);
    }
}
