<?php 

use App\Order;
use App\Concert;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    /** @test */
    public function can_find_an_order_by_confirmation_number()
    {
        $savedOrder = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        $order = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        $this->assertEquals($savedOrder->id, $order->id);
    }

    /** @test */
    public function find_an_order_by_a_nonexistent_confirmation_order_throws_an_exception()
    {
        try {
            Order::findByConfirmationNumber('NONEXISTENTCONFIRMATIONORDER1234');
        } catch (ModelNotFoundException $e) {
            return;
        }

        $this->fail();
    }

    /** @test */
    public function order_has_an_formmated_amount()
    {
        factory(Order::class)->create([
            'amount' => '2500', 
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        $order = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        $this->assertEquals('$25.00', $order->formatted_amount);
    }
}