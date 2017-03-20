<?php 

namespace App\Billing;

interface PaymentGateway
{
    public function getValidTestToken();

    public function charge($amount, $token);

    public function totalCharges();
}