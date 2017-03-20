<?php 

use App\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
        // $this->concert = factory(Concert::class)->states('published')->create()->addTickets(10);
    }

    /** @test */
    public function can_purchase_concert_tickets()
    {
        $this->disableExceptionHandling();
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250
        ])->addTickets(10);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'amount' => 3250 * 3
        ]);
        $order = $concert->orderFor('john@example.com');
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
    }

    /** @test */
    public function an_order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(3);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-token'
        ]);     

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertCount(3, $concert->remainingTickets());
    }

    /** @test */
    public function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(3);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());        
    }

    /** @test */
    public function cannot_purchase_more_tickets_than_remaining()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 11,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertCount(10, $concert->remainingTickets());
    }

    /** @test */
    public function cannot_purchase_tickets_already_been_reserved()
    {
        $this->disableExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);
        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
            $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
                'email' => 'jane@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken()
            ]);

            $response->assertStatus(422);
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());            
        });

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 10,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(201);
        $order = $concert->orderFor('john@example.com');
        $this->assertNotNull($order);
        $this->assertEquals(10, $order->ticketQuantity());
        $this->assertEquals(32500, $this->paymentGateway->totalCharges());
    }
}