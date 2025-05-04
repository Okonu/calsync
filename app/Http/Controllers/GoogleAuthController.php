<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGoogleCalendars;
use App\Models\GoogleAccount;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Color palette for accounts
     */
    protected $colorPalette = [
        '#4285F4', // Google Blue
        '#EA4335', // Google Red
        '#FBBC05', // Google Yellow
        '#34A853', // Google Green
        '#673AB7', // Deep Purple
        '#FF5722', // Deep Orange
        '#795548', // Brown
        '#009688', // Teal
        '#607D8B', // Blue Grey
        '#E91E63', // Pink
    ];

    /**
     * Generate a unique color for a new account
     *
     * @param int $userId
     * @return string
     */
    protected function generateUniqueColor(int $userId): string
    {
        $usedColors = GoogleAccount::where('user_id', $userId)
            ->pluck('color')
            ->toArray();

        $availableColors = array_values(array_diff($this->colorPalette, $usedColors));

        if (count($availableColors) > 0) {
            return $availableColors[array_rand($availableColors)];
        }

        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Redirect the user to the Google authentication page for login/registration.
     */
    public function redirect()
    {
        session(['auth_flow' => 'primary']);

        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/calendar.readonly',
                'https://www.googleapis.com/auth/calendar.events.readonly',
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    /**
     * Handle the callback from Google for login/registration.
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Starting Google callback process (primary login)');

            $googleUser = Socialite::driver('google')->user();
            Log::info('Google user retrieved', ['email' => $googleUser->getEmail()]);

            $existingAccount = GoogleAccount::where('email', $googleUser->getEmail())->first();

            if ($existingAccount) {
                $user = $existingAccount->user;
                Log::info('Existing Google account found, logging in as associated user', [
                    'google_email' => $googleUser->getEmail(),
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);

                $existingAccount->update([
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken ? $googleUser->refreshToken : $existingAccount->refresh_token,
                    'token_expires_at' => Carbon::now()->addSeconds($googleUser->expiresIn),
                ]);

                try {
                    $calendarService = new GoogleCalendarService($existingAccount);
                    $calendarService->syncCalendars();
                    Log::info('Calendars synchronized immediately', ['account_id' => $existingAccount->id]);
                } catch (\Exception $e) {
                    Log::error('Error fetching calendars immediately, falling back to job', [
                        'message' => $e->getMessage(),
                        'account_id' => $existingAccount->id
                    ]);

                    SyncGoogleCalendars::dispatch($existingAccount);
                }

                Auth::login($user);
                return redirect()->route('dashboard');
            }

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'password' => Hash::make(Str::random(24)),
                ]
            );
            Log::info('User found or created', ['id' => $user->id, 'email' => $user->email]);

            $account = new GoogleAccount();
            $account->user_id = $user->id;
            $account->email = $googleUser->getEmail();
            $account->name = $googleUser->getName();
            $account->avatar = $googleUser->getAvatar();
            $account->access_token = $googleUser->token;
            $account->refresh_token = $googleUser->refreshToken ?? null;
            $account->token_expires_at = Carbon::now()->addSeconds($googleUser->expiresIn ?? 3600);
            $account->is_primary = true;
            $account->color = $this->generateUniqueColor($user->id);
            $account->is_active = true;
            $account->save();

            Log::info('Google account created', [
                'id' => $account->id,
                'color' => $account->color
            ]);

            Auth::login($user);
            Log::info('User logged in', ['id' => $user->id]);

            try {
                $calendarService = new GoogleCalendarService($account);
                $calendarService->syncCalendars();
                Log::info('Calendars synchronized immediately', ['account_id' => $account->id]);
            } catch (\Exception $e) {
                Log::error('Error fetching calendars immediately, falling back to job', [
                    'message' => $e->getMessage(),
                    'account_id' => $account->id
                ]);

                SyncGoogleCalendars::dispatch($account);
            }

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            Log::error('Error in Google callback', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/')->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Redirect the user to the Google authentication page for connecting additional accounts.
     */
    public function redirectConnect()
    {
        Log::info('Redirecting to Google for connecting additional account', [
            'user_id' => auth()->id(),
            'connect_redirect' => env('GOOGLE_CONNECT_REDIRECT_URI')
        ]);

        session(['auth_flow' => 'connect']);

        $params = [
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => env('GOOGLE_CONNECT_REDIRECT_URI'),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.readonly https://www.googleapis.com/auth/calendar.events.readonly email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => 'connect_flow_' . auth()->id(),
        ];

        $url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);

        return redirect($url);
    }

    /**
     * Handle the callback from Google for connecting additional accounts.
     */
    public function callbackConnect(Request $request)
    {
        try {
            Log::info('Starting Google connect callback process', [
                'user_id' => auth()->id(),
                'query_params' => $request->all()
            ]);

            $code = $request->input('code');

            if (!$code) {
                throw new \Exception("Authorization code missing from callback request");
            }

            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $code,
                'redirect_uri' => env('GOOGLE_CONNECT_REDIRECT_URI'),
                'grant_type' => 'authorization_code',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to exchange code for token: ' . $response->body());
            }

            $tokenData = $response->json();

            $userInfoResponse = Http::withToken($tokenData['access_token'])
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (!$userInfoResponse->successful()) {
                throw new \Exception('Failed to get user profile: ' . $userInfoResponse->body());
            }

            $googleUser = $userInfoResponse->json();

            $existingAccount = GoogleAccount::where('email', $googleUser['email'])
                ->where('user_id', auth()->id())
                ->first();

            if ($existingAccount) {
                Log::info('Updating existing Google account connection', [
                    'account_id' => $existingAccount->id,
                    'google_email' => $googleUser['email']
                ]);

                $existingAccount->update([
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? $existingAccount->refresh_token,
                    'token_expires_at' => Carbon::now()->addSeconds($tokenData['expires_in'] ?? 3600),
                    'is_active' => true,
                ]);

                $account = $existingAccount;
                $message = 'Google account reconnected successfully!';
            } else {
                $color = $this->generateUniqueColor(auth()->id());

                $account = new GoogleAccount();
                $account->user_id = auth()->id();
                $account->email = $googleUser['email'];
                $account->name = $googleUser['name'] ?? 'Google User';
                $account->avatar = $googleUser['picture'] ?? null;
                $account->access_token = $tokenData['access_token'];
                $account->refresh_token = $tokenData['refresh_token'] ?? null;
                $account->token_expires_at = Carbon::now()->addSeconds($tokenData['expires_in'] ?? 3600);
                $account->is_primary = false;
                $account->color = $color;
                $account->is_active = true;
                $account->save();

                Log::info('New Google account created successfully', [
                    'account_id' => $account->id,
                    'email' => $account->email
                ]);

                $message = 'Google account connected successfully!';
            }

            try {
                Log::info('Synchronizing calendars for connected account', [
                    'account_id' => $account->id
                ]);

                $calendarService = new GoogleCalendarService($account);
                $calendarService->syncCalendars();

                Log::info('Calendars synchronized immediately', [
                    'account_id' => $account->id
                ]);
            } catch (\Exception $e) {
                Log::error('Error fetching calendars immediately, falling back to job', [
                    'message' => $e->getMessage(),
                    'account_id' => $account->id
                ]);

                SyncGoogleCalendars::dispatch($account);
            }

            return redirect()->route('calendar')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error in Google connect callback', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Failed to connect Google account: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
