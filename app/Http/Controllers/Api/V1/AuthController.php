<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:50',
                'unique:customers,phone',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:customers,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'is_active' => true,
            'is_verified' => false,
        ]);

        $token = $customer->createToken(
            'talabetna-mobile'
        )->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully.',
            'data' => [
                'customer' => $this->customerData($customer),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $customer = Customer::where(
            'phone',
            $validated['phone']
        )->first();

        if (
            ! $customer ||
            ! $customer->password ||
            ! Hash::check(
                $validated['password'],
                $customer->password
            )
        ) {
            throw ValidationException::withMessages([
                'phone' => 'The provided credentials are incorrect.',
            ]);
        }

        if (! $customer->is_active) {
            throw ValidationException::withMessages([
                'phone' => 'This customer account is inactive.',
            ]);
        }

        $token = $customer->createToken(
            'talabetna-mobile'
        )->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'customer' => $this->customerData($customer),
                'token' => $token,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->customerData(
                $request->user()
            ),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    protected function customerData(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'avatar' => $customer->avatar,
            'is_verified' => $customer->is_verified,
            'created_at' => $customer->created_at,
        ];
    }
}