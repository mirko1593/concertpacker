<?php

use App\{Concert, Ticket, Order};
use Illuminate\Foundation\Testing\{
    WithoutMiddleware, DatabaseMigrations, DatabaseTransactions
};

class ViewOrderConfirmationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_view_order_confirmation()
    {
        $this->disableExceptionHandling();
        $concert = factory(Concert::class)->states('published')->create();
        $order = factory(Order::class)->create([
            'amount' => '6800',
            'confirmation_number' => 'ORDERCONFIRMATION1234', 
            'card_last_four' => '4242'
        ]);
        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id, 
            'order_id' => $order->id, 
            'code' => 'TICKETCODE1'
        ]);
        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id, 
            'order_id' => $order->id, 
            'code' => 'TICKETCODE2'
        ]);        

        $response = $this->get("/orders/{$order->confirmation_number}");

        $response->assertStatus(200);
        $response->assertViewHas('order', function ($savedOrder) use ($order) {
            return $order->id === $savedOrder->id;
        });
        $response->assertSee('ORDERCONFIRMATION1234');
        $response->assertSee('$68.00');
        $response->assertSee('4242');
        $response->assertSee('TICKETCODE1');
        $response->assertSee('TICKETCODE2');
    }
}