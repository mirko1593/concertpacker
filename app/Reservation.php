<?php 

namespace App;

class Reservation
{
    protected $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public static function reserve($tickets)
    {
        $reservation = new self($tickets);

        return $reservation->reserveTickets();
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    protected function reserveTickets()
    {
        $this->tickets->each(function ($ticket) {
            $ticket->reserve();
        });

        return $this;
    }

    public function getTickets()
    {
        return $this->tickets;
    }

    public function cancel()
    {
        $this->tickets->each(function ($ticket) {
            $ticket->release();
        });
    }
}