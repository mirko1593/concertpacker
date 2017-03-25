<?php 

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway
{
    const TEST_CARD_NUMBER = '4242424242424242';

    protected $charges;

    protected $tokens;

    protected $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback != null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
            // $this->beforeFirstChargeCallback->__invoke($this);
        }
        if (! $this->tokens->has($token)) {
            throw new PaymentFailedException;
        }
        return $this->charges[] = new Charge([
            'amount' => $amount, 
            'card_last_four' => substr($this->tokens[$token], -4)
        ]);
    }

    public function totalCharges()
    {
        return $this->charges->map(function ($charge) {
            return $charge->amount();
        })->sum();
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
    
    public function getValidTestToken($card = self::TEST_CARD_NUMBER)
    {
        $token = 'fake_tok_' . str_random(24);
        $this->tokens[$token] = $card;

        return $token;
    }

    public function chargesDuring($callback)
    {
        $count = $this->charges->count();
        $callback($this);

        return $this->charges->slice($count)->reverse()->values();
    }
}