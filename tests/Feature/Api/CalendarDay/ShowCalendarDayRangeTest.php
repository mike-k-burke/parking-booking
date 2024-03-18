<?php

namespace Tests\Feature\Api\CalendarDay;

use App\Models\CalendarDay;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowCalendarDayRangeTest extends TestCase
{
    use DatabaseTransactions;

    public Carbon $startDate;
    public Carbon $endDate;

    /** @var CalendarDay */
    public $start;
    /** @var CalendarDay */
    public $end;

    public function setUp(): void
    {
        parent::setUp();

        $date = Carbon::create('2124-01-01')->startOfDay();

        $this->start = CalendarDay::factory()->create([
            'date'          => $date,
            'year'          => (int) $date->format('Y'),
            'month'         => (int) $date->format('n'),
            'day'           => (int) $date->format('j'),
            'day_of_week'   => (int) $date->format('N'),
            'is_weekend'    => $date->isWeekend(),
        ]);

        $date = $date->addDay();

        $this->end = CalendarDay::factory()->create([
            'date'          => $date,
            'year'          => (int) $date->format('Y'),
            'month'         => (int) $date->format('n'),
            'day'           => (int) $date->format('j'),
            'day_of_week'   => (int) $date->format('N'),
            'is_weekend'    => $date->isWeekend(),
        ]);
    }

    public function testShowCalendarDayRangeSuccess()
    {
        $response = $this->makeShowCalendarDayRangeRequest($this->start->date->format('Y-m-d'), $this->end->date->format('Y-m-d'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', 2)
                    ->has('data.0', fn (AssertableJson $json) =>
                        $json->where('date', $this->start->date->format('Y-m-d'))
                            ->where('available_spaces', $this->start->available_spaces)
                            ->where('booked_spaces', $this->start->booked_spaces)
                            ->where('has_free_spaces', $this->start->has_free_spaces)
                            ->where('price', $this->start->price)
                            ->etc()
                    )
                    ->has('data.1', fn (AssertableJson $json) =>
                        $json->where('date', $this->end->date->format('Y-m-d'))
                            ->where('available_spaces', $this->end->available_spaces)
                            ->where('booked_spaces', $this->end->booked_spaces)
                            ->where('has_free_spaces', $this->end->has_free_spaces)
                            ->where('price', $this->end->price)
                            ->etc()
                    )
                    ->where('meta.start', $this->start->date->format('Y-m-d'))
                    ->where('meta.end', $this->end->date->format('Y-m-d'))
                    ->where('meta.is_available', $this->start->has_free_spaces && $this->end->has_free_spaces)
                    ->where('meta.price', $this->start->price + $this->end->price)
            );
    }

    public function testShowCalendarDayRangeFailure()
    {
        $response = $this->makeShowCalendarDayRangeRequest('1900-01-01', '1901-01-01');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShowCalendarDayRangeStartWrongFormat()
    {
        $response = $this->makeShowCalendarDayRangeRequest($this->start->date->format('Y-m-d H:i:s'), $this->end->date->format('Y-m-d'));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShowCalendarDayRangeEndWrongFormat()
    {
        $response = $this->makeShowCalendarDayRangeRequest($this->start->date->format('Y-m-d'), $this->end->date->format('Y-m-d H:i:s'));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function makeShowCalendarDayRangeRequest(string $start, string $end): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->get(route('calendar.range', ['start' => $start, 'end' => $end]));
    }
}
