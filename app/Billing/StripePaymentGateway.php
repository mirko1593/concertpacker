<?php 

namespace App\Billing;

use App\Billing\Charge;
use Stripe\{Token, Stripe};
use Stripe\Error\InvalidRequest;
use Stripe\Charge as StripeCharge;

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
            $stripeCharge = StripeCharge::create([
                'amount' => $amount, 
                'source' => $token, 
                'currency' => 'usd'
            ]);
        } catch (InvalidRequest $e) {
            throw new PaymentFailedException($e->getMessage());
        }

        return $this->charges[] = new Charge([
            'id' => $stripeCharge['id'],
            'amount' => $stripeCharge['amount'], 
            'card_last_four' => $stripeCharge['source']['last4']
        ]);
    }

    public function totalCharges()
    {
        return $this->charges->map->amount()->sum();   
    }

    public function getValidTestToken($card = '4242424242424242')
    {
        $token = Token::create([
            "card" => [
                "number" => $card,
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

        return $this->chargesSince($currentLastCharge);
    }

    protected function lastCharge()
    {
        return $this->allCharges()[0];
    }

    protected function chargesSince($ending_before, $limit = 10)
    {
        return $this->stripeChargesSince($ending_before, $limit)->map(function ($stripeCharge) {
            return new Charge([
                'id' => $stripeCharge['id'],
                'amount' => $stripeCharge['amount'], 
                'card_last_four' => $stripeCharge['source']['last4']
            ]);
        });
    }

    protected function stripeChargesSince($ending_before, $limit)
    {
        return $this->allStripeCharges($limit, ['ending_before' => $ending_before->stripeChargeId()]);
    }

    protected function allCharges($limit = 10, $params = [])
    {
        return $this->allStripeCharges($limit, $params)->map(function ($stripeCharge) {
            return new Charge([
                'id' => $stripeCharge['id'],
                'amount' => $stripeCharge['amount'], 
                'card_last_four' => $stripeCharge['source']['last4']
            ]);
        });
    }

    protected function allStripeCharges($limit, $params = [])
    {
        return collect(StripeCharge::all(array_merge([
            'limit' => $limit
        ], $params))['data']);
    }
}