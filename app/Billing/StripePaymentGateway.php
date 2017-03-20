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

    public function getValidTestToken()
    {
        $token = Token::create([
            "card" => [
                "number" => '4242424242424242',
                "exp_month" => 1,
                "exp_year" => date('Y') + 1,
                "cvc" => '314'
            ]
        ]);

        return $token->id;
    }

    public function chargesDuring($callback)
    {
        $currentLastCharge = $this->lastCharge();
        $callback($this);

        return collect($this->chargesSince($currentLastCharge))->pluck('amount')->values();
    }

    public function lastCharge()
    {
        return $this->allCharges()[0];
    }

    public function chargesSince($ending_before, $limit = 10)
    {
        return $this->allCharges($limit, ['ending_before' => $ending_before->id]);
    }

    protected function allCharges($limit = 10, $params = [])
    {
        return Charge::all(array_merge([
            'limit' => $limit
        ], $params))['data'];
    }
}