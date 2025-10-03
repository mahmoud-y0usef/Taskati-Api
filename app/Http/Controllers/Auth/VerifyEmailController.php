<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return redirect('/login')->with('error', 'Invalid verification link.');
        }

        if (!hash_equals(sha1($user->getEmailForVerification()), $request->hash)) {
            return redirect('/login')->with('error', 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('email.verified')->with('message', 'Email already verified. You can now log in.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('email.verified')->with('message', 'Email verified successfully. You can now log in.');
    }

    /**
     * Show the email verification success page.
     */
    public function verified()
    {
        return view('auth.email-verified');
    }
}
