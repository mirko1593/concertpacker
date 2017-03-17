<?php 

use App\Concert;
use Carbon\Carbon;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_retrieve_a_formatted_date()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('May 1, 2017 8:00pm')
        ]);

        $this->assertEquals('May 1, 2017', $concert->formatted_date);
    }

    /** @test */
    public function can_retrieve_a_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('May 1, 2017 8:00pm')
        ]);

        $this->assertEquals('8:00pm', $concert->start_time);
    }

    /** @test */
    public function can_retrieve_a_sell_price()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750
        ]);        

        $this->assertEquals('$67.50', $concert->sellPrice);
    }

    /** @test */
    public function can_retrieve_all_published_concerts()
    {
        $concert1 = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week')
        ]);
        factory(Concert::class, 2)->create();

        $concerts = Concert::published()->get();

        $this->assertCount(1, $concerts);
        $this->assertTrue($concerts->contains($concert1));
    }

    /** @test */
    public function can_reserve_concert_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);

        $reservation = $concert->reserveTickets(10, 'john@example.com');

        $this->assertEquals(32500, $reservation->totalCost());
        $this->assertCount(0, $concert->remainingTickets());        
    }

    /** @test */
    public function can_add_concert_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $concert->addTickets(50);

        $this->assertCount(50, $concert->remainingTickets());
    }

    /** @test */
    public function remaining_tickets_does_not_include_reserved_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        $concert->reserveTickets(25, 'john@example.com');

        $this->assertCount(25, $concert->remainingTickets());
    }

    /** @test */
    public function reserve_more_tickets_than_remaining_will_throw_exception()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        try {
            $concert->reserveTickets(51, 'john@example.com');    
        } catch (NotEnoughTicketsException $e) {
            return;
        }

        $this->fail();
    }

    /** @test */
    public function can_reserve_some_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);
        
        $reservation = $concert->reserveTickets(10, 'john@example.com');

        $this->assertCount(0, $concert->remainingTickets());
        $this->assertEquals(32500, $reservation->totalCost());
    }
}