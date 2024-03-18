<?php

namespace Tests\Feature\Api\Customer;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowCustomerTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Customer */
    public $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create();
    }

    public function testShowCustomerSuccess()
    {
        $response = $this->makeShowCustomerRequest($this->customer->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', $this->customer->id)
                    ->where('email', $this->customer->email)
                    ->where('mobile', $this->customer->mobile)
            );
    }

    public function testShowCustomerFailure()
    {
        $response = $this->makeShowCustomerRequest(-1);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function makeShowCustomerRequest(int $id): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->get(route('customers.show', $id));
    }
}
