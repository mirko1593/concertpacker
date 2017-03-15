<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = ['id'];

    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id');
    }
}
