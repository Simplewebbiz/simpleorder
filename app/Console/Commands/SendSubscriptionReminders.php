<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionReminderMail;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:reminders';
    protected $description = 'Send monthly subscription payment reminders to tenants';

    public function handle(): void
    {
        // Trial ending in 3 days
        $trialEnding = Tenant::where('subscription_status', 'trialing')
            ->whereDate('trial_ends_at', now()->addDays(3)->toDateString())
            ->get();

        foreach ($trialEnding as $tenant) {
            $this->sendReminder($tenant, 'trial_ending');
        }

        // Past due — resend reminder daily
        $pastDue = Tenant::where('subscription_status', 'past_due')->get();

        foreach ($pastDue as $tenant) {
            $this->sendReminder($tenant, 'past_due');
        }

        // Upcoming renewal — 7 days out
        $upcoming = Tenant::where('subscription_status', 'active')
            ->whereDate('subscription_ends_at', now()->addDays(7)->toDateString())
            ->get();

        foreach ($upcoming as $tenant) {
            $this->sendReminder($tenant, 'upcoming');
        }

        $this->info('Subscription reminders sent.');
    }

    private function sendReminder(Tenant $tenant, string $type): void
    {
        // Get the owner email from the tenant's database
        tenancy()->initialize($tenant);
        $owner = \App\Models\Tenant\User::where('role', 'owner')->first();
        tenancy()->end();

        if (!$owner) return;

        Mail::to($owner->email, $owner->name)
            ->send(new SubscriptionReminderMail($tenant, $type));

        $this->line("  Sent {$type} reminder to {$owner->email} ({$tenant->id})");
    }
}
