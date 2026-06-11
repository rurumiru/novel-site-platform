<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('db:sync-pg-to-mysql')->dailyAt('00:00');

Schedule::command('auth:clear-resets')->everyFifteenMinutes();

Schedule::command('app:auto-unlock-chapters')->dailyAt('06:00');

Schedule::command('eriiba:recalc-trust')->dailyAt('03:30');

// Демо-стенд: каждые 15 минут удаляет не-демо контент. Активно только при DEMO_MODE=true.
Schedule::command('demo:cleanup --force')
    ->everyFifteenMinutes()
    ->when(fn () => config('demo.enabled'));
