<?php 

use App\Billing\PaymentGateway;

class FakePaymentGateway implements PaymentGateway
{
    protected $totalCharges;

    public function getValidToken()
    {
        return "valid-token";
    }

    public function getTotalCharges()
    {
        return $this->totalCharges;
    }

    public function charge($amount, $token)
    {
        $this->totalCharges = $amount;
    }
}