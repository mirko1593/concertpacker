<?php 

use App\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConcertOrdersControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
        $this->concert = factory(Concert::class)->states('published')->create();
    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {
        $this->validateField('email', ['email' => '']);               
    }

    /** @test */
    public function email_must_be_a_valid_format_email()
    {
        $this->validateField('email', ['email' => 'not-a-valid-email']);         
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_ticket()
    {
        $this->validateField('ticket_quantity', ['ticket_quantity' => '']);
    }

    /** @test */
    public function ticket_quantity_must_at_least_be_1()
    {
        $this->validateField('ticket_quantity', ['ticket_quantity' => 0]);
    }

    /** @test */
    public function payment_token_is_required_to_purchase_ticket()
    {
        $this->validateField('payment_token', ['payment_token' => '']);
    }

    protected function validateField($field, $data)
    {
        $response = $this->json('POST', "/concerts/{$this->concert->id}/orders", array_merge([
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ], $data));

        $response->assertStatus(422);
        $this->assertArrayHasKey($field, $response->decodeResponseJson());
    }    
}