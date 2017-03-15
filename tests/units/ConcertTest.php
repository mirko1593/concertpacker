<?php 

use App\Concert;
use Carbon\Carbon;
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
    public function can_order_concert_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250
        ]);

        $concert->orderTickets(3, 'john@example.com');

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }
}