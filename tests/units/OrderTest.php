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
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(10);
        $concert->orderTickets(5, 'john@example.com');
        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $order->cancel();

        $this->assertNull(Order::find($order->id));
        $this->assertCount(10, $concert->remainingTickets());
    }
}