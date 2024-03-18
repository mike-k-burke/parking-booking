<?php

namespace App\Http\Controllers;

use App\Actions\Customer\SaveCustomer;
use App\Actions\Customer\ShowCustomer;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function show(string $id, ShowCustomer $action): JsonResponse
    {
        $customer = $action->handle($id);
        return response()->json(new CustomerResource($customer), Response::HTTP_OK);
    }

    public function update(UpdateCustomerRequest $request, int $id, SaveCustomer $action): JsonResponse
    {
        $customer = $action->handle($request->validated(), $id);
        return response()->json(new CustomerResource($customer), Response::HTTP_OK);
    }
}
