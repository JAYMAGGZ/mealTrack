<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ProcessNoShows extends Command
{
    protected $signature   = 'mealtrack:process-no-shows';
    protected $description = 'Mark overdue pending bookings as no-show and release reserved credits';

    public function handle(BookingService $bookingService): void
    {
        $this->info('Processing no-shows at ' . now()->toDateTimeString());

        $processed = $bookingService->processNoShows();

        if ($processed === 0) {
            $this->info('Nothing to process.');
        } else {
            $this->info("Processed {$processed} no-show booking(s).");
        }
    }
}