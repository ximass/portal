<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return response()->json(Customer::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'email|nullable',
            'phone' => 'string|nullable',
            'cnpj' => 'string|nullable',
            'cpf' => 'string|nullable',
            'address' => 'string|nullable',
        ]);

        $customer = Customer::create($data);
        return response()->json($customer, 201);
    }

    public function show(Customer $customer)
    {
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'email|nullable',
            'phone' => 'string|nullable',
            'cnpj' => 'string|nullable',
            'cpf' => 'string|nullable',
            'address' => 'string|nullable',
        ]);

        $customer->update($data);
        return response()->json($customer);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(null, 204);
    }
}