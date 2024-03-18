<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShowCustomer
{
    public function handle(?int $id = null, ?string $email = null): Customer
    {
        if ($id === null && $email === null) {
            throw (new ModelNotFoundException)->setModel(Customer::class);
        }
        if ($id !== null) {
            return Customer::query()->findOrFail($id);
        }
        return Customer::query()->where('email', $email)->firstOrFail();
    }
}
