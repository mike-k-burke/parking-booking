<?php

namespace Tests\Feature\Api\Booking;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowBookingTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Booking */
    public $booking;

    public function setUp(): void
    {
        parent::setUp();

        $this->booking = Booking::factory()->create();
    }

    public function testShowBookingSuccess()
    {
        $response = $this->makeShowBookingRequest($this->booking->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', $this->booking->id)
                    ->where('registration', $this->booking->registration)
                    ->where('start', $this->booking->start->format('Y-m-d'))
                    ->where('end', $this->booking->end->format('Y-m-d'))
                    ->where('price', $this->booking->price)
                    ->where('customer.email', $this->booking->customer->email)
                    ->where('customer.mobile', $this->booking->customer->mobile)
                    ->has('booking_days', $this->booking->booking_days()->count())
                    ->etc()
            );
    }

    public function testShowBookingFailure()
    {
        $response = $this->makeShowBookingRequest(-1);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function makeShowBookingRequest(int $id): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->get(route('bookings.show', $id));
    }
}
