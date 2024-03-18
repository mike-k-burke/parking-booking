<?php

namespace Tests\Feature\Api\CalendarDay;

use App\Models\Booking;
use App\Models\CalendarDay;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateCalendarDaySpacesTest extends TestCase
{
    use DatabaseTransactions;

    /** @var CalendarDay[]|Collection */
    public $days;

    public function setUp(): void
    {
        parent::setUp();

        $date = Carbon::create('2124-01-01')->startOfDay();

        $this->days = collect();
        for ($i = 0; $i < 4; $i++) {
            $this->days->add(
                CalendarDay::factory()->create([
                    'date'              => $date,
                    'year'              => (int) $date->format('Y'),
                    'month'             => (int) $date->format('n'),
                    'day'               => (int) $date->format('j'),
                    'day_of_week'       => (int) $date->format('N'),
                    'is_weekend'        => $date->isWeekend(),
                    'available_spaces'  => 10,
                ])
            );
            $date = $date->addDay();
        }
    }

    public function testUpdateCalendarDaySpacesRangeSuccess()
    {
        $requestData    = $this->getDefaultRequestData();
        $response       = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();

        foreach ($this->days as $day) {
            $this->assertDatabaseHas('calendar_days', [
                'date'              => $day->date->format('Y-m-d'),
                'available_spaces'  => 5,
            ]);
        }
    }

    public function testUpdateCalendarDaySpacesDateSuccess()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['end'] = $requestData['start'];

        $response = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();

        $this->assertDatabaseHas('calendar_days', [
            'date'              => $requestData['start'],
            'available_spaces'  => 5,
        ]);

        foreach ($this->days as $day) {
            if ($day->date->format('Y-m-d') === $requestData['start']) {
                continue;
            }
            $this->assertDatabaseHas('calendar_days', [
                'date'              => $day->date->format('Y-m-d'),
                'available_spaces'  => 10,
            ]);
        }
    }

    public function testUpdateCalendarDaySpacesRangeExludeDateSuccess()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['exclude_days'] = [$requestData['start']];

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();


        $this->assertDatabaseHas('calendar_days', [
            'date'              => $requestData['start'],
            'available_spaces'  => 10,
        ]);

        foreach ($this->days as $day) {
            if ($day->date->format('Y-m-d') === $requestData['start']) {
                continue;
            }
            $this->assertDatabaseHas('calendar_days', [
                'date'              => $day->date->format('Y-m-d'),
                'available_spaces'  => 5,
            ]);
        }
    }

    public function testUpdateCalendarDaySpacesRangeExludeWeekendsSuccess()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['exclude_weekends'] = true;

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();

        foreach ($this->days as $day) {
            $this->assertDatabaseHas('calendar_days', [
                'date'              => $day->date->format('Y-m-d'),
                'available_spaces'  => $day->date->isWeekend() ? 10 : 5,
            ]);
        }
    }

    public function testUpdateCalendarDaySpacesRangeExludeWeekdaysSuccess()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['exclude_weekdays'] = true;

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();

        foreach ($this->days as $day) {
            $this->assertDatabaseHas('calendar_days', [
                'date'              => $day->date->format('Y-m-d'),
                'available_spaces'  => $day->date->isWeekday() ? 10 : 5,
            ]);
        }
    }

    public function testUpdateCalendarDaySpacesRangeStartMissing()
    {
        $requestData = $this->getDefaultRequestData();
        unset($requestData['start']);

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field is required']);
    }

    public function testUpdateCalendarDaySpacesRangeStartWrongFormat()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['start'] .= ' 12:00:00';

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must match the format Y-m-d']);
    }

    public function testUpdateCalendarDaySpacesRangeStartInPast()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['start'] = Carbon::yesterday()->startOfDay()->format('Y-m-d');
        $requestData['end'] = Carbon::now()->startOfDay()->format('Y-m-d');

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must be a date after or equal to today']);
    }

    public function testUpdateCalendarDaySpacesRangeStartAfterEnd()
    {
        $requestData = $this->getDefaultRequestData();
        $start = $requestData['start'];
        $requestData['start'] = $requestData['end'];
        $requestData['end'] = $start;

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must be a date before or equal to end']);
    }

    public function testUpdateCalendarDaySpacesRangeEndMissing()
    {
        $requestData = $this->getDefaultRequestData();
        unset($requestData['end']);

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end' => 'The end field is required']);
    }

    public function testUpdateCalendarDaySpacesRangeEndWrongFormat()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['end'] .= ' 12:00:00';

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end' => 'The end field must match the format Y-m-d']);
    }

    public function testUpdateCalendarDaySpacesRangeTooManyExistingBookings()
    {
        $booking = Booking::factory()->create();

        $requestData = [
            'available_spaces'  => 0,
            'start'             => $booking->start->format('Y-m-d'),
            'end'               => $booking->start->format('Y-m-d'),
        ];

        $response = $this->makeUpdateCalendarDaySpacesRequest($requestData);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'Unable to adjust available spaces for the date ' . $booking->start->format('Y-m-d') . ', too many bookings present']);
    }

    public function testUpdateCalendarDaySpacesRangeDatesNotAvailable()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['start'] = '2224-01-01';
        $requestData['end'] = '2224-01-03';

        $response  = $this->makeUpdateCalendarDaySpacesRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'start' => 'No calendar found record for date 2224-01-01',
                'end' => [
                    'No calendar found record for date 2224-01-02',
                    'No calendar found record for date 2224-01-03'
                ]
            ]);
    }

    protected function getDefaultRequestData(): array
    {
        return [
            'available_spaces'  => 5,
            'start'             => $this->days->first()->date->format('Y-m-d'),
            'end'               => $this->days->last()->date->format('Y-m-d'),
        ];
    }

    protected function makeUpdateCalendarDaySpacesRequest(array $data): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('calendar.update.spaces'), $data);
    }
}
