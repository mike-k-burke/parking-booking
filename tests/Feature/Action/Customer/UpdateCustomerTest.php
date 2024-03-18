<?php

namespace Tests\Feature\Action\Customer;

use App\Actions\Customer\SaveCustomer;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UpdateCustomerTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Customer */
    public $customer;

    /** @var SaveCustomer */
    protected $action;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create();
        $this->action = resolve(SaveCustomer::class);
    }

    public function testUpdateCustomerSuccess()
    {
        $attributes = $this->getDefaultAttributes();
        $customer   = $this->action->handle($attributes, $this->customer->id,);

        $this->assertEquals($this->customer->id, $customer->id);
        $this->assertEquals('test@test.com', $customer->email);
        $this->assertEquals('+4401234567890', $customer->mobile);
    }

    public function testUpdateBookingFailure()
    {
        $this->expectException(ModelNotFoundException::class);

        $attributes = $this->getDefaultAttributes();
        $this->action->handle($attributes, -1);
    }

    protected function getDefaultAttributes(): array
    {
        return [
            'email'     => 'test@test.com',
            'mobile'    => '+4401234567890'
        ];
    }
}
