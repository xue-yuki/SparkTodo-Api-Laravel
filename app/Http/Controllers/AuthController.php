<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $auth;

    public function __construct()
    {
        // Initialize Firebase Auth
        // Note: You'll need to set FIREBASE_CREDENTIALS in .env
        $factory = (new Factory)->withServiceAccount(env('FIREBASE_CREDENTIALS'));
        $this->auth = $factory->createAuth();
    }

    /**
     * Login with Google via Firebase ID Token
     */
    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            // Verify Firebase ID token
            $verifiedIdToken = $this->auth->verifyIdToken($request->id_token);
            $uid = $verifiedIdToken->claims()->get('sub');
            $firebaseUser = $this->auth->getUser($uid);

            // Get user info
            $email = $firebaseUser->email;
            $name = $firebaseUser->displayName ?? explode('@', $email)[0];
            $photoUrl = $firebaseUser->photoUrl ?? null;

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make(uniqid()), // Random password, not used
                ]
            );

            // Create Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'token' => $token,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Firebase token',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Logout (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
            ],
        ], 200);
    }
}
