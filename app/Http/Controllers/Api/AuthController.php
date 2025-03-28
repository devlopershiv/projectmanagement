<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // 🔹 User Login API (Returns Token)
    public function login(Request $request)
    {
        // **Response Structure**
        $response = [
            'access_token' => null,
            'user'         => null,
            'success'      => false,
            'message'      => '',
        ];

        // **Step 1: Validate Request**
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 422);
        }

        // **Step 2: Logging Login Attempt**
        Log::info('Login Attempt', ['email' => $request->email]);

        // **Step 3: Find User by Email**
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $response['message'] = 'User does not exist';
            return response()->json($response, 404);
        }

        // **Step 4: Verify Password**
        if (!Hash::check($request->password, $user->password)) {
            $response['message'] = 'Incorrect password';
            return response()->json($response, 401);
        }

        // **Step 5: Generate Token**
        $accessToken = $user->createToken('auth_token')->plainTextToken;

        // **Step 6: Authenticate User**
        Auth::login($user);
        $user = User::find($user->id);

        // **Step 7: Prepare Success Response**
        $response = [
            'access_token' => $accessToken,
            'user'         => $user,
            'success'      => true,
            'message'      => 'Login successful',
        ];

        return response()->json($response, 200);
    }

    // 🔹 User Logout API (Revokes Token)
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // 🔹 Get Authenticated User Details
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
