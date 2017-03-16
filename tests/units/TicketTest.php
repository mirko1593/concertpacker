<?php 

use App\Ticket;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketTest extends TestCase
{
    use DatabaseMigrations;
    
    /** @test */
    public function a_ticket_can_be_reserved()
    {
        $ticket = factory(Ticket::class)->states('unreserved')->create();

        $ticket->reserve();

        $this->assertNotNull($ticket->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_released()
    {
        $ticket = factory(Ticket::class)->states('reserved')->create();

        $ticket->release();

        $this->assertNull($ticket->reserved_at);
    }
}