<?php 

use App\Order;
use App\Ticket;
use App\Concert;
use App\OrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @setUp */
    public function setUp()
    {
        parent::setUp();
      
    }

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
        $orderConfirmationNumberGenerator = Mockery::mock(OrderConfirmationNumberGenerator::class, [
            'generate' => 'ORDERCONFIRMATION1234'
        ]);

        $this->app->instance(OrderConfirmationNumberGenerator::class, $orderConfirmationNumberGenerator);          
        $order = factory(Order::class)->create([
            'email' => 'john@example.com', 
            'amount' => 6000,
            'confirmation_number' => Order::generateNumber()
        ]);
        $order->tickets()->saveMany(factory(Ticket::class)->times(5)->create());

        $result = $order->toArray();

        $this->assertEquals($result, [
            'email' => 'john@example.com', 
            'ticket_quantity' => 5,
            'amount' => 6000, 
            'confirmation_number' => 'ORDERCONFIRMATION1234'
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