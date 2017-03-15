<?php

namespace App;

use App\Ticket;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function cancel()
    {
        $this->tickets->each(function ($ticket) {
            $ticket->update(['order_id' => null]);
        });
        $this->delete();
    }
}
