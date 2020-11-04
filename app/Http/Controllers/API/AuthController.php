<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'company_address' => 'required',
            'company_contact' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        };

        $input = $request->all();
        $input["email"] = strtolower($input["email"]);
        $input['password'] = bcrypt($input['password']);
        $input["role"] = "admin";

        $user = User::where('email', $input["email"])->get();
        if ($user->isEmpty()) {
            $admin_user = User::create($input);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully. Please login.',
                'user' => $admin_user
            ], 201);
        }
        return response()->json([
            'success' => false,
            'error' => 'An account with that email already exist.'
        ], 400);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        };
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $accessToken = auth()->user()->createToken('authToken')->accessToken;
            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'token' => $accessToken,
                'user' => Auth::user(),
            ], 200);
        }
        return response()->json([
            'success' => false,
            'error' => 'Invalid credentials.'
        ], 401);
    }
}
