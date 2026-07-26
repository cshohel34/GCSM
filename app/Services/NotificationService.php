<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * Central dispatcher: in-app panel (always), email (Laravel Mail), WhatsApp (HTTP).
 * Recipients are User models (staff). A crew phone/email can be passed separately.
 */
class NotificationService
{
    public function __construct(protected WhatsAppService $whatsapp) {}

    /**
     * @param iterable<User> $users
     * @param array $channels subset of ['panel','email','whatsapp']
     */
    public function notify(iterable $users, string $type, string $title, ?string $body = null, ?string $link = null, array $channels = ['panel']): void
    {
        foreach ($users as $user) {
            if (in_array('panel', $channels)) {
                AppNotification::create([
                    'user_id' => $user->id, 'type' => $type, 'title' => $title, 'body' => $body, 'link' => $link,
                ]);
            }
            if (in_array('email', $channels) && $user->email) {
                $this->email($user->email, $title, $body ?? '');
            }
            if (in_array('whatsapp', $channels) && $user->phone) {
                $this->whatsapp->send($user->phone, $title.($body ? "\n".$body : ''));
            }
        }
    }

    /** Notify an external contact (crew) by email + whatsapp only. */
    public function notifyContact(?string $email, ?string $phone, string $title, ?string $body = null, array $channels = ['email', 'whatsapp']): void
    {
        if (in_array('email', $channels) && $email) $this->email($email, $title, $body ?? '');
        if (in_array('whatsapp', $channels) && $phone) $this->whatsapp->send($phone, $title.($body ? "\n".$body : ''));
    }

    public function admins(): Collection
    {
        return User::where('status', 'active')
            ->where(fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['Super Admin', 'Admin'])))
            ->get();
    }

    protected function email(string $to, string $subject, string $body): void
    {
        try {
            Mail::raw($body ?: $subject, function ($m) use ($to, $subject) {
                $m->to($to)->subject($subject);
            });
        } catch (\Throwable $e) {
            \Log::warning('[Mail:error] '.$e->getMessage());
        }
    }
}
