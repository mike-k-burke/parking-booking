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

class ShowCalendarDayTest extends TestCase
{
    use DatabaseTransactions;

    public Carbon $startDate;
    public Carbon $endDate;

    /** @var CalendarDay */
    public $calendarDay;

    public function setUp(): void
    {
        parent::setUp();

        $date = Carbon::create('2124-01-01')->startOfDay();

        $this->calendarDay = CalendarDay::factory()->create([
            'date'              => $date,
            'year'              => (int) $date->format('Y'),
            'month'             => (int) $date->format('n'),
            'day'               => (int) $date->format('j'),
            'day_of_week'       => (int) $date->format('N'),
            'is_weekend'        => $date->isWeekend(),
        ]);
    }

    public function testShowCalendarDaySuccess()
    {
        $response = $this->makeShowCalendarDayRequest($this->calendarDay->date->format('Y-m-d'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('date', $this->calendarDay->date->format('Y-m-d'))
                    ->where('available_spaces', $this->calendarDay->available_spaces)
                    ->where('booked_spaces', $this->calendarDay->booked_spaces)
                    ->where('has_free_spaces', $this->calendarDay->has_free_spaces)
                    ->where('price', $this->calendarDay->price)
            );
    }

    public function testShowCalendarDayFailure()
    {
        $response = $this->makeShowCalendarDayRequest('1900-01-01');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShowCalendarDayWrongFormat()
    {
        $response = $this->makeShowCalendarDayRequest($this->calendarDay->date->format('Y-m-d H:i:s'));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function makeShowCalendarDayRequest(string $date): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->get(route('calendar.show', $date));
    }
}
