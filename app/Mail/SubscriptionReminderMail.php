<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public string $type = 'upcoming', // upcoming | past_due | trial_ending
    ) {}

    public function build()
    {
        $subjects = [
            'upcoming'     => 'Your SimpleOrder subscription renews soon',
            'past_due'     => 'Action required: SimpleOrder payment failed',
            'trial_ending' => 'Your SimpleOrder trial ends in 3 days',
        ];

        return $this
            ->subject($subjects[$this->type] ?? 'SimpleOrder Subscription Update')
            ->from(config('mail.from.address'), config('app.name'))
            ->view('emails.subscription-reminder')
            ->with([
                'tenant'     => $this->tenant,
                'type'       => $this->type,
                'plan'       => $this->tenant->plan,
                'billingUrl' => config('app.url') . '/dashboard/billing',
                'portalUrl'  => config('app.url') . '/dashboard/billing/portal',
            ]);
    }
}
