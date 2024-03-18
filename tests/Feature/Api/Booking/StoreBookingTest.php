<?php

namespace Tests\Feature\Api\Booking;

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

class StoreBookingTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testStoreBookingSuccess()
    {
        $freeDays   = $this->getFreeDays(3);
        $startDay   = $freeDays->first();
        $endDay     = $freeDays->last();

        $requestData    = $this->getDefaultRequestData($freeDays);
        $response       = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMissingValidationErrors()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('registration', 'TT12TT')
                    ->where('start', $startDay->date->format('Y-m-d'))
                    ->where('end', $endDay->date->format('Y-m-d'))
                    ->where('price', $freeDays->sum('price'))
                    ->where('customer.email', 'test@test.com')
                    ->where('customer.mobile', '+4401234567890')
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

    public function testStoreBookingRegistrationMissing()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        unset($requestData['registration']);

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['registration' => 'The registration field is required']);
    }

    public function testStoreBookingRegistrationTooLong()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['registration'] = 'TT1 2TT1234567890';

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['registration' => 'The registration field must not be greater than 15 characters']);
    }

    public function testStoreBookingStartMissing()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        unset($requestData['start']);

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field is required']);
    }

    public function testStoreBookingStartWrongFormat()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['start'] .= ' 12:00:00';

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must match the format Y-m-d']);
    }

    public function testStoreBookingStartInPast()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['start'] = Carbon::yesterday()->startOfDay()->format('Y-m-d');

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must be a date after or equal to today']);
    }

    public function testStoreBookingStartAfterEnd()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $start = $requestData['start'];
        $requestData['start'] = $requestData['end'];
        $requestData['end'] = $start;

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must be a date before or equal to end']);
    }

    public function testStoreBookingEndMissing()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        unset($requestData['end']);

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end' => 'The end field is required']);
    }

    public function testStoreBookingEndWrongFormat()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['end'] .= ' 12:00:00';

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end' => 'The end field must match the format Y-m-d']);
    }

    public function testStoreBookingDatesNotAvailable()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['start'] = '2124-01-01';
        $requestData['end'] = '2124-01-03';

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'start' => 'No available spaces for the date 2124-01-01',
                'end' => [
                    'No available spaces for the date 2124-01-02',
                    'No available spaces for the date 2124-01-03'
                ]
            ]);
    }

    public function testStoreBookingEmailMissing()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        unset($requestData['email']);

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email' => 'The email field is required']);
    }

    public function testStoreBookingEmailWrongFormat()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['email'] = 'this is not an email address';

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email' => 'The email field must be a valid email address']);
    }

    public function testStoreBookingPasswordTooShort()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['password'] = 'test';
        $requestData['password_confirmation'] = 'test';

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password' => 'The password field must be at least 6 characters']);
    }

    public function testStoreBookingPasswordNotConfirmed()
    {
        $requestData = $this->getDefaultRequestData($this->getFreeDays(3));
        $requestData['password'] = 'testing';

        $response = $this->makeStoreBookingRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password' => 'The password field confirmation does not match']);
    }

    protected function getFreeDays($dayCount = 1): Collection
    {
        do {
            $startDay = CalendarDay::where('date', '>', now()->startOfDay())->inRandomOrder()->first();
            $days = CalendarDay::where('date', '>=', $startDay->date)->orderBy('date')->take($dayCount)->get();
            $days->filter(fn (CalendarDay $day) => $day->has_free_spaces);
        } while ($days->count() !== $dayCount);

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
            'email'         => 'test@test.com',
            'mobile'        => '+4401234567890'
        ];
    }

    protected function makeStoreBookingRequest(array $data): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('bookings.store'), $data);
    }
}
