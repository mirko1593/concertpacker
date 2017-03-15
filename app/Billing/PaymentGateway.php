<?php 

namespace App\Billing;

interface PaymentGateway
{
    public function getValidToken();

    public function charge($amount, $token);

    public function totalCharges();
}