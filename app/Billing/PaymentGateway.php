<?php 

namespace App\Billing;

interface PaymentGateway
{
    public function getValidToken($card, $cvc);

    public function charge($amount, $token);

    public function totalCharges();
}