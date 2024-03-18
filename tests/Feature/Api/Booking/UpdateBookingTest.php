<?php

namespace Tests\Feature\Api\Booking;

use App\Models\Booking;
use App\Models\CalendarDay;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateBookingTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Booking */
    public $booking;

    public function setUp(): void
    {
        parent::setUp();

        $this->booking = Booking::factory()->create();
    }

    public function testUpdateBookingSuccess()
    {
        $freeDays   = $this->getFreeDays(3);
        $startDay   = $freeDays->first();
        $endDay     = $freeDays->last();

        $requestData    = $this->getDefaultRequestData($freeDays);
        $response       = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', $this->booking->id)
                    ->where('registration', 'TT12TT')
                    ->where('start', $startDay->date->format('Y-m-d'))
                    ->where('end', $endDay->date->format('Y-m-d'))
                    ->where('price', $freeDays->sum('price'))
                    ->where('customer.email', $this->booking->customer->email)
                    ->where('customer.mobile', $this->booking->customer->mobile)
                    ->has('booking_days', 3)
                    ->has('booking_days.0', fn (AssertableJson $json) =>
                        $json->where('date', $startDay->date->format('Y-m-d'))
                            ->where('price', $startDay->price)
                            ->etc()
                    )
                    ->has('booking_days.1', fn (AssertableJson $json) =>
                        $json->where('date', $startDay->date->clone()->addDay()->format('Y-m-d'))
                            ->where('price', $freeDays[1]->price)
                            ->etc()
                    )
                    ->has('booking_days.2', fn (AssertableJson $json) =>
                        $json->where('date', $endDay->date->format('Y-m-d'))
                            ->where('price', $endDay->price)
                            ->etc()
                    )
                    ->etc()
            );
    }

    public function testUpdateBookingFailure()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));

        $response = $this->makeUpdateBookingRequest(-1, $requestData);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateBookingRegistrationTooLong()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['registration'] = 'TT1 2TT1234567890';

        $response = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['registration' => 'The registration field must not be greater than 15 characters']);
    }

    public function testUpdateBookingStartMissing()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        unset($requestData['start']);

        $response = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field is required']);
    }

    public function testUpdateBookingStartWrongFormat()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['start'] .= ' 12:00:00';

        $response = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must match the format Y-m-d']);
    }

    public function testUpdateBookingStartInPast()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['start'] = Carbon::yesterday()->startOfDay()->format('Y-m-d');

        $response = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must be a date after or equal to today']);
    }

    public function testUpdateBookingStartAfterEnd()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $start = $requestData['start'];
        $requestData['start'] = $requestData['end'];
        $requestData['end'] = $start;

        $response = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must be a date before or equal to end']);
    }

    public function testUpdateBookingEndMissing()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        unset($requestData['end']);

        $response = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end' => 'The end field is required']);
    }

    public function testUpdateBookingEndWrongFormat()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['end'] .= ' 12:00:00';

        $response = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end' => 'The end field must match the format Y-m-d']);
    }

    public function testUpdateBookingDatesNotAvailable()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['start'] = '2124-01-01';
        $requestData['end'] = '2124-01-03';

        $response = $this->makeUpdateBookingRequest($this->booking->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'start' => 'No available spaces for the date 2124-01-01',
                'end' => [
                    'No available spaces for the date 2124-01-02',
                    'No available spaces for the date 2124-01-03'
                ]
            ]);
    }

    protected function getFreeDays($dayCount = 1): Collection
    {
        do {
            $startDay = CalendarDay::where('date', '>', now()->startOfDay())->inRandomOrder()->first();
            $days = CalendarDay::where('date', '>=', $startDay->date)->orderBy('date')->take($dayCount)->get();
            $days->filter(fn (CalendarDay $day) => $day->has_free_spaces);
        } while ($days->count() !== $dayCount || $days->contains($this->booking->start->format('Y-m-d')) || $days->contains($this->booking->end->format('Y-m-d')));

        return $days;
    }

    protected function getDefaultRequestData(Collection $days): array
    {
        $startDay   = $days->first();
        $endDay     = $days->last();

        return [
            'registration'  => 'TT1 2TT',
            'start'         => $startDay->date->format('Y-m-d'),
            'end'           => $endDay->date->format('Y-m-d'),
        ];
    }

    protected function makeUpdateBookingRequest(int $id, array $data): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->put(route('bookings.update', $id), $data);
    }
}
