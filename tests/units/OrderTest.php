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
    public function create_an_order_with_reservation()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);
        $reservation = $concert->reserveTickets(10, 'john@example.com');    
        
        $order = Order::withReservation($reservation);

        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(10, $order->ticketQuantity());
        $this->assertEquals(32500, $order->amount);
        $this->assertCount(0, $concert->remainingTickets());
    }

    /** @test */
    public function order_can_be_converted_to_array()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);
        $reservation = $concert->reserveTickets(5, 'john@example.com');
        $order = Order::withReservation($reservation);

        $result = $order->toArray();

        $this->assertEquals($result, [
            'email' => 'john@example.com', 
            'ticket_quantity' => 5,
            'amount' => 5 * 3250
        ]);        
    }
}