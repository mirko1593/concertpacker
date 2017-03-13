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

        $this->visit('concerts/' . $concert->id);

        $this->see('Title');
        $this->see('SubTitle');
        $this->see('$32.50');
        $this->see('Venue');
        $this->see('Venue Address');
        $this->see('City');
        $this->see('State');
        $this->see('100000');
        $this->see('May 1, 2017');
        $this->see('8:00pm');
        $this->see('Additional Information');
    }

    /** @test */
    public function cannot_view_an_unpublished_concert_detail()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();

        $this->get('concerts/' . $concert->id);

        $this->assertResponseStatus(404);
    }
}