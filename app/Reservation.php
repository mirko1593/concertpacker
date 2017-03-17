<?php 

namespace App;

class Reservation
{
    protected $tickets;

    protected $email;

    public function __construct($tickets, $email = null)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public static function reserve($tickets, $email)
    {
        $reservation = new self($tickets, $email);

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

    public function getEmail()
    {
        return $this->email;
    }

    public function cancel()
    {
        $this->tickets->each(function ($ticket) {
            $ticket->release();
        });
    }

    public function toArray()
    {
        return [
            'email' => $this->getEmail(), 
            'amount' => $this->totalCost()
        ];
    }

    public function complete()
    {
        return Order::withReservation($this);
    }
}