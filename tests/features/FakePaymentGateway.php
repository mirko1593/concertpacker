<?php 

use App\Billing\PaymentGateway;
use App\Billing\PaymentFailedException;

class FakePaymentGateway implements PaymentGateway
{
    protected $totalCharges;

    public function getValidToken()
    {
        return "valid-token";
    }

    public function getInvalidToken()
    {
        return 'invalid-token';
    }

    public function getTotalCharges()
    {
        return $this->totalCharges;
    }

    public function charge($amount, $token)
    {
        if ($token != $this->getValidToken()) {
            throw new PaymentFailedException;
        }
        $this->totalCharges = $amount;
    }
}