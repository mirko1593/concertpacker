<?php 

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FakePaymentGatewayTest extends TestCase
{
    public function setUp()
    {
        $this->paymentGateway = new FakePaymentGateway;
    }

    /** @test */
    public function charge_with_a_valid_token_succeed()
    {
        $this->paymentGateway->charge(9750, $this->paymentGateway->getValidToken());

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
    }

    /** @test */
    public function charge_with_an_invalid_token_failed()
    {
        try {
            $this->paymentGateway->charge(9750, 'invalid-token');
        } catch (PaymentFailedException $e) {
            return;
        }

        $this->fail();
    }
}