<?php 

namespace App\Billing;

use Stripe\Error\InvalidRequest;
use Stripe\{Token, Charge, Stripe};

class StripePaymentGateway implements PaymentGateway
{
    protected $charges;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->charges = collect();
    }

    public function charge($amount, $token)
    {
        try {
            $charge = Charge::create([
                'amount' => $amount, 
                'source' => $token, 
                'currency' => 'usd'
            ]);
        } catch (InvalidRequest $e) {
            throw new PaymentFailedException($e->getMessage());
        }

        $this->charges[] = $charge;
    }

    public function totalCharges()
    {
        return $this->charges->sum('amount');   
    }

    public function getValidToken($card, $cvc)
    {
        $token = Token::create([
            "card" => [
                "number" => $card,
                "exp_month" => 1,
                "exp_year" => date('Y') + 1,
                "cvc" => $cvc
            ]
        ]);

        return $token->id;
    }

    public function lastCharge($ending_before = null)
    {
        $lastCharges = $ending_before === null 
            ? $this->allCharges(1)
            : $this->allCharges(1, ['ending_before' => $ending_before->id]);

        return $lastCharges[0];
    }

    protected function allCharges($limit = 10, $params = [])
    {
        return Charge::all(array_merge([
            'limit' => $limit
        ], $params))['data'];
    }
}