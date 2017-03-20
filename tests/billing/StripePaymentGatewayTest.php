<?php 

use Stripe\{Token, Charge, Stripe};
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\{
    WithoutMiddleware, DatabaseMigrations, DatabaseTransactions
};

/**
 * @group internet
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayTestCase;

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway;
    }
}