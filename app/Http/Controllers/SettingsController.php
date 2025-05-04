<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGoogleCalendars;
use App\Models\Calendar;
use App\Models\GoogleAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

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

    public function syncAccount($id)
    {
        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        SyncGoogleCalendars::dispatch($account);

        return response()->json([
            'success' => true,
            'message' => 'Calendar sync initiated'
        ]);
    }

    public function deleteAccount($id)
    {
        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        Calendar::where('google_account_id', $account->id)->delete();

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}
