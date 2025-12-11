<?php

namespace App\Http\Controllers;

use App\Jobs\SyncMicrosoftCalendars;
use App\Models\MicrosoftAccount;
use App\Services\MicrosoftCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MicrosoftAuthController extends Controller
{
    protected $colorPalette = [
        '#0078D4', // Microsoft Blue
        '#2B579A', // Dark Blue
        '#367C2B', // Office Green
        '#D83B01', // Office Orange
        '#A4373A', // Office Red
        '#744DA9', // Teams Purple
        '#008272', // Teal
        '#107C10', // Xbox Green
        '#00B7C3', // Cyan
        '#E3008C', // Magenta
    ];

    protected function generateUniqueColor(int $userId): string
    {
        $usedColors = MicrosoftAccount::where('user_id', $userId)
            ->pluck('color')
            ->toArray();

        $availableColors = array_values(array_diff($this->colorPalette, $usedColors));

        if (count($availableColors) > 0) {
            return $availableColors[array_rand($availableColors)];
        }

        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    public function redirectConnect()
    {
        Log::info('Redirecting to Microsoft for connecting additional account', [
            'user_id' => auth()->id(),
            'connect_redirect' => config('services.microsoft.connect_redirect')
        ]);

        session(['auth_flow' => 'connect']);

        $params = [
            'client_id' => config('services.microsoft.client_id'),
            'redirect_uri' => config('services.microsoft.connect_redirect'),
            'response_type' => 'code',
            'scope' => 'User.Read Calendars.Read Calendars.ReadWrite offline_access',
            'response_mode' => 'query',
            'state' => 'connect_flow_' . auth()->id(),
        ];

        $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . http_build_query($params);

        return redirect($url);
    }

    public function callbackConnect(Request $request)
    {
        try {
            Log::info('Starting Microsoft connect callback process', [
                'user_id' => auth()->id(),
                'query_params' => $request->all()
            ]);

            $code = $request->input('code');

            if (!$code) {
                throw new \Exception("Authorization code missing from callback request");
            }

            $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
                'client_id' => config('services.microsoft.client_id'),
                'client_secret' => config('services.microsoft.client_secret'),
                'code' => $code,
                'redirect_uri' => config('services.microsoft.connect_redirect'),
                'grant_type' => 'authorization_code',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to exchange code for token: ' . $response->body());
            }

            $tokenData = $response->json();

            $userInfoResponse = Http::withToken($tokenData['access_token'])
                ->get('https://graph.microsoft.com/v1.0/me');

            if (!$userInfoResponse->successful()) {
                throw new \Exception('Failed to get user profile: ' . $userInfoResponse->body());
            }

            $microsoftUser = $userInfoResponse->json();

            $existingAccount = MicrosoftAccount::where('email', $microsoftUser['mail'] ?? $microsoftUser['userPrincipalName'])
                ->where('user_id', auth()->id())
                ->first();

            if ($existingAccount) {
                Log::info('Updating existing Microsoft account connection', [
                    'account_id' => $existingAccount->id,
                    'microsoft_email' => $microsoftUser['mail'] ?? $microsoftUser['userPrincipalName']
                ]);

                $existingAccount->update([
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? $existingAccount->refresh_token,
                    'token_expires_at' => Carbon::now()->addSeconds($tokenData['expires_in'] ?? 3600),
                    'is_active' => true,
                ]);

                $account = $existingAccount;
                $message = 'Microsoft account reconnected successfully!';
            } else {
                $color = $this->generateUniqueColor(auth()->id());

                $account = new MicrosoftAccount();
                $account->user_id = auth()->id();
                $account->email = $microsoftUser['mail'] ?? $microsoftUser['userPrincipalName'];
                $account->name = $microsoftUser['displayName'] ?? 'Microsoft User';
                $account->avatar = null; // Microsoft Graph doesn't return avatar URL directly, needs another call
                $account->access_token = $tokenData['access_token'];
                $account->refresh_token = $tokenData['refresh_token'] ?? null;
                $account->token_expires_at = Carbon::now()->addSeconds($tokenData['expires_in'] ?? 3600);
                $account->is_primary = false;
                $account->color = $color;
                $account->is_active = true;
                $account->save();

                Log::info('New Microsoft account created successfully', [
                    'account_id' => $account->id,
                    'email' => $account->email
                ]);

                $message = 'Microsoft account connected successfully!';
            }

            try {
                Log::info('Synchronizing calendars for connected account', [
                    'account_id' => $account->id
                ]);

                $calendarService = new MicrosoftCalendarService($account);
                $calendarService->syncCalendars();

                Log::info('Calendars synchronized immediately', [
                    'account_id' => $account->id
                ]);
            } catch (\Exception $e) {
                Log::error('Error fetching calendars immediately, falling back to job', [
                    'message' => $e->getMessage(),
                    'account_id' => $account->id
                ]);

                SyncMicrosoftCalendars::dispatch($account);
            }

            return redirect()->route('calendar')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error in Microsoft connect callback', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Failed to connect Microsoft account: ' . $e->getMessage());
        }
    }
}
