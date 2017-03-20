<?php 

use Stripe\Token;
use Stripe\Charge;
use Stripe\Stripe;
use App\Billing\StripePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group internet
 */
class StripePaymentGatewayTest extends TestCase
{
    /** @test */
    public function can_charges_with_a_valid_token()
    {
        $paymentGateway = new StripePaymentGateway();
        $currentLastCharge = $paymentGateway->lastCharge();

        $paymentGateway->charge(3500, $paymentGateway->getValidTestToken());

        $lastCharge = $paymentGateway->lastCharge($currentLastCharge);
        
        $this->assertNotNull($lastCharge);
        $this->assertEquals(3500, $lastCharge->amount);
        $this->assertEquals(3500, $paymentGateway->totalCharges());
    }

    /** @test */
    public function charge_with_an_invalid_token_failed()
    {
        $paymentGateway = new StripePaymentGateway();
        
        try {
            $paymentGateway->charge(2500, 'invalid-token');
        } catch (PaymentFailedException $e) {
            $this->assertEquals(0, $paymentGateway->totalCharges());
            return;
        }

        $this->fail();
    }
}