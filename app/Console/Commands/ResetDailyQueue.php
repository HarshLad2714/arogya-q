<?php

namespace App\Console\Commands;

use App\Enums\TokenStatus;
use App\Models\Token;
use App\Services\QueueEngineService;
use Illuminate\Console\Command;

class ResetDailyQueue extends Command
{
    protected $signature = 'queue:daily-reset';

    protected $description = 'Mark missed visits as no-shows and reset today\'s queue counters';

    public function handle(QueueEngineService $queue): int
    {
        $missed = Token::query()
            ->whereDate('date', '<', today())
            ->whereIn('status', [TokenStatus::Booked, TokenStatus::Arrived])
            ->update(['status' => TokenStatus::NoShow]);

        $queue->reset(today()->toDateString());

        $this->info("No-shows marked: {$missed}. Queue reset for ".today()->toDateString());

        return self::SUCCESS;
    }
}
