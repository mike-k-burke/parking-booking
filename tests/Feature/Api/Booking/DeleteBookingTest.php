<?php

namespace Tests\Feature\Api\Booking;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteBookingTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Booking */
    public $booking;

    public function setUp(): void
    {
        parent::setUp();

        $this->booking = Booking::factory()->create();
    }

    public function testDeleteBookingSuccess()
    {
        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
        ]);

        $response = $this->makeDeleteBookingRequest($this->booking->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT)
            ->assertNoContent();

        $this->assertDatabaseMissing('bookings', [
            'id' => $this->booking->id,
        ]);
    }

    public function testDeleteBookingFailure()
    {
        $response = $this->makeDeleteBookingRequest(-1);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function makeDeleteBookingRequest(int $id): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->delete(route('bookings.destroy', $id));
    }
}
