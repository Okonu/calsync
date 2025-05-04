<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGoogleCalendars;
use App\Models\Calendar;
use App\Models\GoogleAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the account settings page
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

    /**
     * Update account color
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAccountColor(Request $request, $id)
    {
        $request->validate([
            'color' => 'required|string',
        ]);

        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $account->update([
            'color' => $request->color,
        ]);

        return response()->json([
            'success' => true,
            'account' => $account,
        ]);
    }

    /**
     * Update account status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAccountStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $account->update([
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'account' => $account,
        ]);
    }

    /**
     * Trigger sync for account
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncAccount($id)
    {
        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Dispatch sync job
        SyncGoogleCalendars::dispatch($account);

        return response()->json([
            'success' => true,
            'message' => 'Calendar sync initiated'
        ]);
    }

    /**
     * Delete account
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount($id)
    {
        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Delete associated calendars
        Calendar::where('google_account_id', $account->id)->delete();

        // Delete the account
        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}
