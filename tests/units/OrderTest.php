<?php 

use App\Order;
use App\Concert;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function order_can_be_canceled()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);
        $concert->orderTickets(5, 'john@example.com');
        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $order->cancel();

        $this->assertNull(Order::find($order->id));
        $this->assertCount(10, $concert->remainingTickets());
    }

    /** @test */
    public function order_can_be_converted_to_array()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);
        $concert->orderTickets(5, 'john@example.com');
        $order = $concert->orderFor('john@example.com');

        $result = $order->toArray();

        $this->assertEquals($result, [
            'email' => 'john@example.com', 
            'ticket_quantity' => 5,
            'amount' => 5 * 3250
        ]);        
    }
}