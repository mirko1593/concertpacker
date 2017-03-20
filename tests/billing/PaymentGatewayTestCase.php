<?php 

use App\Billing\PaymentFailedException;

trait PaymentGatewayTestCase
{
    abstract public function getPaymentGateway();

    /** @test */
    public function can_charges_with_a_valid_token()
    {
        $paymentGateway = $this->getPaymentGateway();

        $lastCharges = $paymentGateway->chargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(3500, $paymentGateway->getValidTestToken());
        });
        
        $this->assertEquals(3500, $lastCharges->sum());
        $this->assertEquals(3500, $paymentGateway->totalCharges());
    }

    /** @test */
    public function charge_with_an_invalid_token_failed()
    {
        $paymentGateway = $this->getPaymentGateway();
        
        try {
            $paymentGateway->charge(2500, 'invalid-token');
        } catch (PaymentFailedException $e) {
            $this->assertEquals(0, $paymentGateway->totalCharges());
            return;
        }

        $this->fail();
    }    

   /** @test */
    public function can_retrieve_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $lastCharges = $paymentGateway->chargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
        });

        $this->assertEquals(10000, $paymentGateway->totalCharges());
        $this->assertEquals([4000, 3000], $lastCharges->toArray());
    }     
}