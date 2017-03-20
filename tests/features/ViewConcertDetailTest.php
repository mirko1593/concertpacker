<?php 

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertDetailTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_view_a_published_concert_detail()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->get('concerts/' . $concert->id);

        $response->assertStatus(200);
        $response->assertSee('Title');
        $response->assertSee('SubTitle');
        $response->assertSee('$32.50');
        $response->assertSee('Venue');
        $response->assertSee('Venue Address');
        $response->assertSee('City, State 100000');
        $response->assertSee('May 1, 2017');
        $response->assertSee('8:00pm');
        $response->assertSee('Additional Information');
    }

    /** @test */
    public function cannot_view_an_unpublished_concert_detail()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();

        $response = $this->get('concerts/' . $concert->id);

        $response->assertStatus(404);
    }
}