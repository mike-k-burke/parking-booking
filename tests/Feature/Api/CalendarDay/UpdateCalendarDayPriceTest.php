<?php

namespace Tests\Feature\Api\CalendarDay;

use App\Models\CalendarDay;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateCalendarDayPriceTest extends TestCase
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
                    'date'          => $date,
                    'year'          => (int) $date->format('Y'),
                    'month'         => (int) $date->format('n'),
                    'day'           => (int) $date->format('j'),
                    'day_of_week'   => (int) $date->format('N'),
                    'is_weekend'    => $date->isWeekend(),
                    'price'         => 1000,
                ])
            );
            $date = $date->addDay();
        }
    }

    public function testUpdateCalendarDayPriceRangeSuccess()
    {
        $requestData    = $this->getDefaultRequestData();
        $response       = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();

        foreach ($this->days as $day) {
            $this->assertDatabaseHas('calendar_days', [
                'date'  => $day->date->format('Y-m-d'),
                'price' => 5000,
            ]);
        }
    }

    public function testUpdateCalendarDayPriceDateSuccess()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['end'] = $requestData['start'];

        $response = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();

        $this->assertDatabaseHas('calendar_days', [
            'date'  => $requestData['start'],
            'price' => 5000,
        ]);

        foreach ($this->days as $day) {
            if ($day->date->format('Y-m-d') === $requestData['start']) {
                continue;
            }
            $this->assertDatabaseHas('calendar_days', [
                'date'  => $day->date->format('Y-m-d'),
                'price' => 1000,
            ]);
        }
    }

    public function testUpdateCalendarDayPriceRangeExludeDateSuccess()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['exclude_days'] = [$requestData['start']];

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();


        $this->assertDatabaseHas('calendar_days', [
            'date'  => $requestData['start'],
            'price' => 1000,
        ]);

        foreach ($this->days as $day) {
            if ($day->date->format('Y-m-d') === $requestData['start']) {
                continue;
            }
            $this->assertDatabaseHas('calendar_days', [
                'date'  => $day->date->format('Y-m-d'),
                'price' => 5000,
            ]);
        }
    }

    public function testUpdateCalendarDayPriceRangeExludeWeekendsSuccess()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['exclude_weekends'] = true;

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();

        foreach ($this->days as $day) {
            $this->assertDatabaseHas('calendar_days', [
                'date'  => $day->date->format('Y-m-d'),
                'price' => $day->date->isWeekend() ? 1000 : 5000,
            ]);
        }
    }

    public function testUpdateCalendarDayPriceRangeExludeWeekdaysSuccess()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['exclude_weekdays'] = true;

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors();

        foreach ($this->days as $day) {
            $this->assertDatabaseHas('calendar_days', [
                'date'  => $day->date->format('Y-m-d'),
                'price' => $day->date->isWeekday() ? 1000 : 5000,
            ]);
        }
    }

    public function testUpdateCalendarDayPriceRangeStartMissing()
    {
        $requestData = $this->getDefaultRequestData();
        unset($requestData['start']);

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field is required']);
    }

    public function testUpdateCalendarDayPriceRangeStartWrongFormat()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['start'] .= ' 12:00:00';

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must match the format Y-m-d']);
    }

    public function testUpdateCalendarDayPriceRangeStartInPast()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['start'] = Carbon::yesterday()->startOfDay()->format('Y-m-d');
        $requestData['end'] = Carbon::now()->startOfDay()->format('Y-m-d');

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must be a date after or equal to today']);
    }

    public function testUpdateCalendarDayPriceRangeStartAfterEnd()
    {
        $requestData = $this->getDefaultRequestData();
        $start = $requestData['start'];
        $requestData['start'] = $requestData['end'];
        $requestData['end'] = $start;

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start' => 'The start field must be a date before or equal to end']);
    }

    public function testUpdateCalendarDayPriceRangeEndMissing()
    {
        $requestData = $this->getDefaultRequestData();
        unset($requestData['end']);

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end' => 'The end field is required']);
    }

    public function testUpdateCalendarDayPriceRangeEndWrongFormat()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['end'] .= ' 12:00:00';

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end' => 'The end field must match the format Y-m-d']);
    }

    public function testUpdateCalendarDayPriceRangeDatesNotAvailable()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['start'] = '2224-01-01';
        $requestData['end'] = '2224-01-03';

        $response  = $this->makeUpdateCalendarDayPriceRequest($requestData);

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
            'price' => 5000,
            'start' => $this->days->first()->date->format('Y-m-d'),
            'end'   => $this->days->last()->date->format('Y-m-d'),
        ];
    }

    protected function makeUpdateCalendarDayPriceRequest(array $data): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('calendar.update.price'), $data);
    }
}
