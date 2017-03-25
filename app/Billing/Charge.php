<?php 

namespace App\Billing;

class Charge 
{
    protected $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function amount()
    {
        return $this->data['amount'];
    }

    public function cardLastFour()
    {
        return $this->data['card_last_four'];
    }

    public function stripeChargeId()
    {
        return $this->data['id'];
    }
}