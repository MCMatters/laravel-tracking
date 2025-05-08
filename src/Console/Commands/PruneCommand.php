<?php

declare(strict_types=1);

namespace McMatters\LaravelTracking\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use McMatters\LaravelTracking\Models\Tracking;

class PruneCommand extends Command
{
    protected $signature = 'tracking:prune {--days=30: The number of days to retain data}';

    protected $description = 'Prune stale entries from the database';

    public function handle(): int
    {
        $countPruned = 0;

        $this->getQuery()
            ->eachById(static function (Tracking $tracking) use (&$countPruned) {
                $tracking->delete();

                $countPruned++;
            });

        $this->info("{$countPruned} entries pruned.");

        return self::SUCCESS;
    }

    protected function getQuery(): Builder
    {
        $days = (int) $this->option('days');
        $days = max($days, 1);

        return Tracking::query()->where(
            'created_at',
            '<',
            Carbon::parse("-{$days} days"),
        );
    }
}
