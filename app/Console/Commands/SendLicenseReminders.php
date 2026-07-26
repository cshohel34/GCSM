<?php

namespace App\Console\Commands;

use App\Models\CompanyLicense;
use App\Models\LicenseReminder;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/** Module 7: from 1 month before a licence expiry, remind daily until renewed (LM-03). */
class SendLicenseReminders extends Command
{
    protected $signature = 'license:reminders {--dry-run}';
    protected $description = 'Send daily company-licence expiry reminders (from 30 days before).';

    public function handle(NotificationService $notifier): int
    {
        $today = now()->startOfDay();
        $sent = 0;

        CompanyLicense::whereNotNull('expiry_date')->chunkById(200, function ($licenses) use ($today, &$sent) {
            foreach ($licenses as $lic) {
                $expiry = $lic->expiry_date->startOfDay();
                $daysLeft = $today->diffInDays($expiry, false); // negative if past
                if ($daysLeft > 30) continue; // only within a month or already expired

                $exists = LicenseReminder::where('company_license_id', $lic->id)
                    ->whereDate('sent_for_date', $today)->exists();
                if ($exists) continue;

                if (! $this->option('dry-run')) {
                    $title = "Licence expiring: {$lic->name}";
                    $body = "{$lic->name} (".($lic->license_no ?: 'no #').") expires {$lic->expiry_date->toDateString()}.";
                    $notifier->notify($notifier->admins(), 'license_expiry', $title, $body, url('/licenses'), ['panel','email','whatsapp']);
                    LicenseReminder::create([
                        'company_license_id' => $lic->id,
                        'expiry_date' => $lic->expiry_date->toDateString(),
                        'sent_for_date' => $today->toDateString(),
                        'channels' => ['email', 'whatsapp', 'panel'],
                        'sent_at' => now(),
                    ]);
                }
                $sent++;
            }
        });

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '')."Licence reminders: {$sent}");
        return self::SUCCESS;
    }
}
