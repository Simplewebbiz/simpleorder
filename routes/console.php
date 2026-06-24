<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('subscriptions:reminders')->dailyAt('09:00');
Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();