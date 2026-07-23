<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $customer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_CUSTOMER,
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
        $orders = Order::where('user_id', Auth::guard('tenant')->id())
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
    }
}
