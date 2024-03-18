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

class UpdateCustomerTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Customer */
    public $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create();
    }

    public function testUpdateCustomerSuccess()
    {
        $requestData    = $this->getDefaultRequestData();
        $response       = $this->makeUpdateCustomerRequest($this->customer->id, $requestData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonMissingValidationErrors()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', $this->customer->id)
                    ->where('email', 'test@test.com')
                    ->where('mobile', '+4401234567890')
            );
    }
    public function testUpdateBookingFailure()
    {
        $requestData    = $this->getDefaultRequestData();
        $response       = $this->makeUpdateCustomerRequest(-1, $requestData);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateCustomerEmailWrongFormat()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['email'] = 'this is not an email address';

        $response = $this->makeUpdateCustomerRequest($this->customer->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email' => 'The email field must be a valid email address']);
    }

    public function testStoreBookingPasswordTooShort()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['password'] = 'test';
        $requestData['password_confirmation'] = 'test';

        $response = $this->makeUpdateCustomerRequest($this->customer->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password' => 'The password field must be at least 6 characters']);
    }

    public function testStoreBookingPasswordNotConfirmed()
    {
        $requestData = $this->getDefaultRequestData();
        $requestData['password'] = 'testing';

        $response = $this->makeUpdateCustomerRequest($this->customer->id, $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password' => 'The password field confirmation does not match']);
    }

    protected function getDefaultRequestData(): array
    {
        return [
            'email'     => 'test@test.com',
            'mobile'    => '+4401234567890'
        ];
    }

    protected function makeUpdateCustomerRequest(int $id, array $data): TestResponse
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        return $this->withHeaders(['Accept' => 'application/json'])
            ->put(route('customers.update', $id), $data);
    }
}
