<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
                // 'email' => 'required|string|email|max:200|unique:users',
                // 'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:200',
            'username' => 'required|string|max:50',
            'password' => 'required|string|min:8|max:50',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom tidak valid!',
                'errors' => $validator->errors()
            ], 400);
        }
    
        try {
        
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'status' => 0, // Non-aktif sampai email diverifikasi
                'account_type' => 1, // Default account type
            ]);
        
            // Generate verif email
            $encryptedEmail = encrypt($request->email);
        
            // URL frontend untuk verifikasi email
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        
            // Kirim email verifikasi
            Mail::to($user->email)->send(new VerifyEmail($encryptedEmail, $frontendUrl));
        
            return response()->json([
                'code' => 201,
                'status' => 'success',
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            // Return error response if something goes wrong
            return response()->json([
                'code' => 500,
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
        
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'emailUsername' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->emailUsername)->where('status', 1)->first();

        if (!$user) {
            $user = User::where('username', $request->emailUsername)->where('status', 1)->first();
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'emailUsername' => ['The account is not registered or has not been verified!'],
            ]);
        }


        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'emailUsername' => ['Email/username or password is incorrect!'],
            ]);
        }

        // Buat token dengan masa berlaku 10 jam
        $token = $user->createToken('auth_token', ['*'], now()->addHours(10))->plainTextToken;

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Login successfuly',
            'token' => $token,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            // dd($request->data);
            // Decrypt the email from the request
            $email = decrypt($request->data);

            // Find the user with the given email and status = 0
            $user = User::where('status', 0)->where('email', $email)->first();

            // Check if the user exists
            if (!$user) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failed',
                    'message' => 'User not found or already verified'
                ], 404);
            }

            // Update the user's status and email verification timestamp
            $user->update([
                'status' => 1,
                'email_verified_at' => now()
            ]);

            // Return success response
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Email successfully verified',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            // Return error response if something goes wrong
            return response()->json([
                'code' => 500,
                'status' => 'failed',
                'message' => 'Failed to verify email: ' . $e->getMessage()
            ], 500);
        }
    }
}
