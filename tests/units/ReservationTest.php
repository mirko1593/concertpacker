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
        $tickets = $concert->findTickets(10);

        $reservation = new Reservation($tickets);

        $this->assertEquals(32500, $reservation->totalCost());
    }
}