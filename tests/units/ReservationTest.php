<?php 

use App\Concert;
use App\Reservation;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;
    
    /** @test */
    public function calculate_the_total_cost_of_an_reservation()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);
        
        $reservation = $concert->reserveTickets(10, 'john@example.com');

        $this->assertEquals(32500, $reservation->totalCost());
        $this->assertEquals('john@example.com', $reservation->getEmail());
    }

    /** @test */
    public function reservation_can_be_canceled()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);  
        $reservation = $concert->reserveTickets(5, 'john@example.com');

        $reservation->cancel();

        $this->assertCount(10, $concert->remainingTickets());
    }

    /** @test */
    public function reservation_can_be_converted_to_array()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);  
        $reservation = $concert->reserveTickets(10, 'john@example.com');

        $result = $reservation->toArray();

        $this->assertEquals($result, [
            'email' => 'john@example.com', 
            'amount' => 32500
        ]);        
    }
}