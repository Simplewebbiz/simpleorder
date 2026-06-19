<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('tenant')->login($customer);

        return response()->json(['message' => 'Registered successfully.']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::guard('tenant')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return response()->json(['message' => 'Logged in.']);
    }

    public function logout(Request $request)
    {
        Auth::guard('tenant')->logout();
        return response()->json(['message' => 'Logged out.']);
    }

    public function orders(Request $request)
    {
        $orders = Order::where('customer_email', Auth::guard('tenant')->user()->email)
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
    }
}
