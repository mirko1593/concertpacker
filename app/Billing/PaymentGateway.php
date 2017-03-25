<?php 

namespace App\Billing;

interface PaymentGateway
{
    public function getValidTestToken($card);

    public function charge($amount, $token);

    public function totalCharges();
}