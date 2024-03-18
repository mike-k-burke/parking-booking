<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SaveCustomer
{
    public function __construct(private ShowCustomer $showAction) {}

    public function handle(array $attributes, ?int $id = null): Customer
    {
        $email = Arr::get($attributes, 'email');

        /**
         * If passed an ID for the customer, retrieve and update that record.
         * If not, but an email address is passed, check to see if there is an
         * existing customer record for that email. Otherwise createa new record.
         */
        if ($id !== null) {
            $customer = $this->showAction->handle(id: $id);
        } elseif($email !== null) {
            try {
                $customer = $this->showAction->handle(email: $email);
            } catch (ModelNotFoundException $e) {
                $customer = new Customer();
            }
        }

        $attributes = Arr::only($attributes, ['email', 'mobile', 'password']);
        if (isset($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        $customer->fill($attributes);
        $customer->save();

        return $customer;
    }
}
