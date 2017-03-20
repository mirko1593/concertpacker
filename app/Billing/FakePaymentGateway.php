<?php 

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway
{
    protected $charges;

    protected $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback != null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
            // $this->beforeFirstChargeCallback->__invoke($this);
        }
        if ($token != $this->getValidTestToken()) {
            throw new PaymentFailedException;
        }
        $this->charges[] = $amount;   
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
    
    public function getValidTestToken()
    {
        return 'valid-token';
    }
}