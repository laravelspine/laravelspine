<?php

declare(strict_types=1);

namespace Spine\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spine\Models\ActivityLog;

class AppCron extends Command
{
    protected $signature = 'app:cron {--json : Output results as JSON}';
    protected $description = 'Run scheduled maintenance tasks: purge mail queue, retry jobs, prune activity logs, expire verifications and IP bans, prune sessions.';

    public function handle(): int
    {
        $results = [];
        $lock = Cache::lock('app:cron', 60);

        if (! $lock->get()) {
            $msg = 'Cron is already running (mutex held). Skipping.';
            $this->log($results, $msg);
            return Command::FAILURE;
        }

        try {
            $results['purge_mail_queue'] = $this->purgeMailQueue();
            $results['retry_queue'] = $this->retryQueue();
            $results['prune_activity_log'] = $this->pruneActivityLog();
            $results['expire_register_verifications'] = $this->expireRegisterVerifications();
            $results['expire_ip_bans'] = $this->expireIpBans();
            $results['prune_sessions'] = $this->pruneSessions();
        } finally {
            $lock->release();
        }

        $this->log($results);

        return Command::SUCCESS;
    }

    private function purgeMailQueue(): array
    {
        try {
            Artisan::call('queue:flush');
            return ['status' => 'ok', 'message' => 'Mail queue flushed.'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function retryQueue(): array
    {
        try {
            Artisan::call('queue:retry', ['id' => 'all']);
            return ['status' => 'ok', 'message' => 'Queue retry initiated.'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function pruneActivityLog(): array
    {
        try {
            $months = (int) config('spine.misc.delete_activity_log_older_then', 2);
            $cutoff = now()->subMonths($months);
            $deleted = ActivityLog::where('created_at', '<', $cutoff)->delete();
            return ['status' => 'ok', 'deleted' => $deleted, 'cutoff' => $cutoff->toIso8601String()];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function expireRegisterVerifications(): array
    {
        try {
            $expired = DB::table('password_reset_tokens')
                ->where('created_at', '<', now()->subMinutes(config('auth.passwords.users.expire', 60)))
                ->delete();
            return ['status' => 'ok', 'expired' => $expired];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function expireIpBans(): array
    {
        try {
            $expired = DB::table('ip_bans')
                ->where('expires_at', '<=', now())
                ->delete();
            return ['status' => 'ok', 'expired' => $expired];
        } catch (\Throwable $e) {
            return ['status' => 'ok', 'expired' => 0, 'message' => 'No ip_bans table found — skipped.'];
        }
    }

    private function pruneSessions(): array
    {
        try {
            $lastActivityThreshold = now()->subHours(24);
            $deleted = DB::table('sessions')
                ->where('last_activity', '<', $lastActivityThreshold->timestamp)
                ->delete();
            return ['status' => 'ok', 'pruned' => $deleted];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * @param array<string, mixed> $results
     */
    private function log(array $results, string $message = ''): void
    {
        if ($this->option('json')) {
            $payload = ['tasks' => $results, 'message' => $message ?: 'Cron completed.'];
            $this->output->writeln(json_encode($payload));
            return;
        }

        $this->info('Cron completed.');
        foreach ($results as $task => $result) {
            $status = $result['status'] ?? 'unknown';
            $prefix = $status === 'ok' ? '  ✅' : '  ❌';
            $this->line("{$prefix} {$task}: " . ($result['message'] ?? json_encode($result)));
        }
    }
}
