<?php

use Illuminate\Support\Facades\Schedule;

// Daily OMA sync (one-way OMA -> GCSM) and expiry reminders.
Schedule::command('oma:sync-new')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('oma:sync-updates')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('crew:document-reminders')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('license:reminders')->dailyAt('08:15')->withoutOverlapping();

// Job-placement lifecycle: flip resting crew daily; remind Super Admin weekly.
Schedule::command('crew:lifecycle-flip')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('crew:urgency-digest')->weeklyOn(1, '08:30')->withoutOverlapping();
