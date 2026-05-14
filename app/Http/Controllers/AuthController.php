<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Redirect the user to Google's OAuth consent screen.
     * This is a full-page redirect, not an XHR request.
     * Ad blockers cannot block full-page navigations.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        $url = Socialite::driver('google')->redirect()->getTargetUrl();

        return redirect($url . '&prompt=select_account');
    }

    /**
     * Handle the callback from Google after the user authorizes.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google authentication failed. Please try again.');
        }

        // Whitelist check: only users whose email exists in the users table can log in.
        // Admins pre-create user records to grant access.
        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect('/login')->with('error', 'Your email is not authorized. Contact an administrator.');
        }

        if (! $user->is_active) {
            return redirect('/login')->with('error', 'Your account has been deactivated. Contact an administrator.');
        }

        // Store the Google subject ID on first login.
        // This links the local user record to the Google account permanently.
        if (! $user->google_sub) {
            $user->update([
                'google_sub' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'name' => $googleUser->getName(),
            ]);
        } else {
            // Update avatar on subsequent logins (it may change)
            $user->update([
                'avatar_url' => $googleUser->getAvatar(),
            ]);
        }

        // Create a session-based login. Laravel stores the session in the database
        // (because SESSION_DRIVER=database in .env).
        Auth::login($user, remember: true);

        return redirect('/dashboard')->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Log the user out and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate the session and regenerate the CSRF token to prevent
        // session fixation attacks.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}