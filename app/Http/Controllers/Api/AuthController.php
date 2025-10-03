<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail', 'resendVerification']]);
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Log the incoming request data for debugging

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Manual password confirmation check
        if ($request->password !== $request->password_confirmation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => [
                    'password_confirmation' => ['The password confirmation does not match.']
                ]
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        // Log successful registration
        Log::info('User registered successfully', [
            'email' => $user->email,
            'user_id' => $user->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully. Please verify your email address.',
            'user' => $user->only(['id', 'name', 'email']),
            'email_verification_sent' => true
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Check if email is verified before attempting token
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && !$user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email address before logging in.',
                'email_verified' => false,
                'can_resend_verification' => true,
                'user_email' => $user->email
            ], 403);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = auth()->user();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => auth()->user(),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth()->user()
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return response()->json([
            'status' => 'success',
            'user' => auth()->user(),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Send password reset link to user's email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found with this email address'
            ], 404);
        }

        // Generate password reset token
        $token = Str::random(64);

        // Delete existing password reset tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Create new password reset token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // Send password reset email with error handling
        try {
            $user->notify(new ResetPasswordNotification($token, $request->email));

            // Log successful email sending
            Log::info('Password reset email sent successfully', [
                'email' => $request->email,
                'user_id' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset link sent to your email'
            ]);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to send password reset email', [
                'email' => $request->email,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // If email sending fails, clean up the token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send password reset email. Please try again later.',
                'error_details' => config('app.debug') ? $e->getMessage() : 'Email service unavailable'
            ], 500);
        }
    }

    /**
     * Reset password using token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if password reset token exists and is valid
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired password reset token'
            ], 400);
        }

        // Check if token is expired (60 minutes)
        $tokenCreatedAt = Carbon::parse($passwordReset->created_at);
        if ($tokenCreatedAt->addMinutes(60)->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Password reset token has expired'
            ], 400);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete password reset token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully'
        ]);
    }

    /**
     * Verify user email
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return redirect('/login')->with('error', 'Invalid verification link.');
        }

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect('/login')->with('error', 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('email.verified')->with('message', 'Email already verified. You can now log in.');
        }

        $user->markEmailAsVerified();

        Log::info('Email verified successfully', [
            'email' => $user->email,
            'user_id' => $user->id
        ]);

        return redirect()->route('email.verified')->with('message', 'Email verified successfully. You can now log in.');
    }

    /**
     * Resend email verification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email is already verified'
            ], 400);
        }

        try {
            $user->sendEmailVerificationNotification();

            return response()->json([
                'status' => 'success',
                'message' => 'Verification email sent successfully'
            ]);
        } catch (\Exception $e) {


            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification email. Please try again later.'
            ], 500);
        }
    }

    /**
     * Update user profile information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $updated = false;

        if ($request->has('name') && $request->name !== $user->name) {
            $user->name = $request->name;
            $updated = true;
        }

        if ($request->has('email') && $request->email !== $user->email) {
            $user->email = $request->email;
            $user->email_verified_at = null; // Reset email verification if email changed
            $updated = true;
        }

        if ($updated) {
            $user->save();

            // Send email verification if email was changed
            if ($request->has('email') && $request->email !== $user->getOriginal('email')) {
                try {
                    $user->sendEmailVerificationNotification();
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Profile updated successfully. A verification email has been sent to your new email address.',
                        'user' => $user->only(['id', 'name', 'email']),
                        'email_verification_sent' => true,
                        'email_verification_required' => true,
                        'note' => 'Please check your email and verify your new email address to continue using the account.'
                    ]);
                } catch (\Exception $e) {
                    // Log the error but still return success for profile update
                    Log::error('Failed to send email verification after profile update', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Profile updated successfully, but failed to send verification email.',
                        'user' => $user->only(['id', 'name', 'email']),
                        'email_verification_sent' => false,
                        'email_verification_required' => true,
                        'note' => 'Please manually request email verification from the settings.'
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user->only(['id', 'name', 'email'])
            ]);
        }

        return response()->json([
            'status' => 'info',
            'message' => 'No changes detected'
        ]);
    }

    /**
     * Update user password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log password change
        Log::info('Password updated successfully', [
            'email' => $user->email,
            'user_id' => $user->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Update user profile image
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        try {
            // Delete old image if exists
            if ($user->image && file_exists(public_path('storage/' . $user->image))) {
                unlink(public_path('storage/' . $user->image));
            }

            // Store new image
            $imagePath = $request->file('image')->store('profile_images', 'public');

            // Update user image path
            $user->update([
                'image' => $imagePath
            ]);

            // Log image update
            Log::info('Profile image updated successfully', [
                'email' => $user->email,
                'user_id' => $user->id,
                'image_path' => $imagePath
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile image updated successfully',
                'user' => $user->only(['id', 'name', 'email', 'image']),
                'image_url' => asset('storage/' . $imagePath)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update profile image', [
                'email' => $user->email,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile image. Please try again.'
            ], 500);
        }
    }
}
