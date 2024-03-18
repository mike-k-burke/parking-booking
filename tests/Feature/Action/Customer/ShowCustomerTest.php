<?php

namespace Tests\Feature\Action\Customer;

use App\Actions\Customer\ShowCustomer;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ShowCustomerTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Customer */
    public $customer;

    /** @var ShowCustomer */
    protected $action;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create();
        $this->action = resolve(ShowCustomer::class);
    }

    public function testShowCustomerSuccess()
    {
        $customer = $this->action->handle($this->customer->id);

        $this->assertEquals($this->customer->id, $customer->id);
        $this->assertEquals($this->customer->email, $customer->email);
        $this->assertEquals($this->customer->mobile, $customer->mobile);
    }

    public function testShowCustomerFailure()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->action->handle(-1);
    }
}
