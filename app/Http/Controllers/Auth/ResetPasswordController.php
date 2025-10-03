<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    /**
     * Show the password reset form
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        // Validate that both token and email are provided
        if (!$token || !$email) {
            return redirect()->back()->with('error', 'Invalid password reset link.');
        }

        // Check if the token exists and is valid
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$passwordReset) {
            return redirect()->back()->with('error', 'Invalid or expired password reset token.');
        }

        // Check if token is expired (60 minutes)
        $tokenCreatedAt = Carbon::parse($passwordReset->created_at);
        if ($tokenCreatedAt->addMinutes(60)->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();
                
            return redirect()->back()->with('error', 'Password reset token has expired. Please request a new one.');
        }

        // Show the reset form
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Handle the password reset form submission
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function resetPassword(Request $request)
    {
        // Validate the form input
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if password reset token exists and is valid
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return redirect()->back()
                ->with('error', 'Invalid or expired password reset token.')
                ->withInput();
        }

        // Check if token is expired (60 minutes)
        $tokenCreatedAt = Carbon::parse($passwordReset->created_at);
        if ($tokenCreatedAt->addMinutes(60)->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
                
            return redirect()->back()
                ->with('error', 'Password reset token has expired. Please request a new one.')
                ->withInput();
        }

        // Find the user and update password
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return redirect()->back()
                ->with('error', 'User not found.')
                ->withInput();
        }

        // Update user password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete password reset token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Log the password reset for security
        \Log::info('Password reset completed successfully', [
            'email' => $request->email,
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Show success message
        return view('auth.password-reset-success')->with('success', 'Your password has been reset successfully! You can now log in with your new password.');
    }
}
