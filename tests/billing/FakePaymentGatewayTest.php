<?php 

use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\{
    WithoutMiddleware, DatabaseMigrations, DatabaseTransactions
};

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayTestCase;

    protected function getPaymentGateway()
    {
        return new FakePaymentGateway;
    }

    /** @test */
    public function running_a_hook_before_first_charge()
    {
        $callbackRan = false;
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$callbackRan) {
            $callbackRan = true;
            // $paymentGateway->charge(100, 'valid-token');
            $this->assertEquals(0, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(1000, 'valid-token');

        $this->assertTrue($callbackRan);
        $this->assertEquals(1000, $paymentGateway->totalCharges());
    }
}