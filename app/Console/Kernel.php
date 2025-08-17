protected function schedule(Schedule $schedule): void
{
    // Check low stock every hour
    $schedule->command('stock:check-low')
        ->hourly()
        ->withoutOverlapping();

    // Generate stock reports daily
    $schedule->command('stock:generate-report')
        ->dailyAt('08:00');
}