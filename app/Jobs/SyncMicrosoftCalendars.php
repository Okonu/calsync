<?php

namespace App\Jobs;

use App\Models\MicrosoftAccount;
use App\Services\MicrosoftCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMicrosoftCalendars implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected $account;

    public function __construct(MicrosoftAccount $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        if (!$this->account->is_active) {
            return;
        }

        try {
            Log::info('Starting Microsoft calendar sync', ['account_id' => $this->account->id, 'email' => $this->account->email]);

            $service = new MicrosoftCalendarService($this->account);
            $service->syncCalendars();

            $this->account->update([
                'last_synced_at' => now(),
                'sync_failed' => false,
                'sync_error' => null,
            ]);

            Log::info('Microsoft calendar sync completed', ['account_id' => $this->account->id, 'email' => $this->account->email]);
        } catch (\Exception $e) {
            Log::error('Microsoft calendar sync failed', [
                'account_id' => $this->account->id,
                'email' => $this->account->email,
                'error' => $e->getMessage()
            ]);

            if ($this->attempts() === $this->tries) {
                $this->account->update([
                    'sync_failed' => true,
                    'sync_error' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        Log::error('Microsoft calendar sync job failed', [
            'account_id' => $this->account->id,
            'email' => $this->account->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
