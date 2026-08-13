<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(Request $request)
    {
        if (Auth::check() || $request->query('action') === 'connect') {
            session(['google_action' => 'connect']);
        } else {
            session(['google_action' => 'login']);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->with('error', 'Gagal menghubungkan dengan Google. Silakan coba lagi.');
        }

        $action = session()->pull('google_action', 'login');
        $googleId = $googleUser->getId();
        $googleEmail = $googleUser->getEmail();

        // SCENARIO 1: User is connecting Google account from Profile page
        if (Auth::check() || $action === 'connect') {
            $currentUser = Auth::user();

            if (! $currentUser) {
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }

            // Check if another user already linked this Google ID / Email
            $existingUser = User::where(function ($q) use ($googleId, $googleEmail) {
                $q->where('google_id', $googleId)
                  ->orWhere('google_email', $googleEmail);
            })->where('id', '!=', $currentUser->id)->first();

            if ($existingUser) {
                return redirect()->route('profile.edit')->with('error', "Akun Google ($googleEmail) sudah terhubung dengan pengguna lain di LMS!");
            }

            // Update current user's Google info
            $currentUser->update([
                'google_id' => $googleId,
                'google_email' => $googleEmail,
            ]);

            return redirect()->route('profile.edit')->with('status', "Akun Google ($googleEmail) berhasil dihubungkan!");
        }

        // SCENARIO 2: Guest logging in via Google
        $user = User::where('google_id', $googleId)
            ->orWhere('google_email', $googleEmail)
            ->orWhere('email', $googleEmail)
            ->first();

        if (! $user) {
            return redirect()->route('login')->with('error', "Akun Google ($googleEmail) belum terhubung dengan akun LMS mana pun. Silakan login dengan password terlebih dahulu dan hubungkan akun Google Anda di menu Profil.");
        }

        // Link google_id / google_email if not set yet
        if (! $user->google_id || ! $user->google_email) {
            $user->update([
                'google_id' => $googleId,
                'google_email' => $googleEmail,
            ]);
        }

        Auth::login($user, true);

        // Redirect based on role
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'tatausaha' => redirect()->route('tatausaha.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            default => redirect()->route('siswa.dashboard'),
        };
    }

    /**
     * Disconnect Google account from current user profile.
     */
    public function disconnectGoogle(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->update([
                'google_id' => null,
                'google_email' => null,
            ]);
        }

        return redirect()->route('profile.edit')->with('status', 'Hubungan akun Google berhasil diputuskan.');
    }
}
