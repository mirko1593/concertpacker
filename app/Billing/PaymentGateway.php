<?php 

namespace App\Billing;

interface PaymentGateway
{
    public function getValidToken();

    public function getTotalCharges();

    public function charge($amount, $token);
}