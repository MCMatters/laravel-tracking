<?php

declare(strict_types=1);

namespace McMatters\LaravelTracking\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use McMatters\LaravelTracking\Models\Tracking;

use function max;

class PruneCommand extends Command
{
    protected $signature = 'tracking:prune {--days=30: The number of days to retain data}';

    protected $description = 'Prune stale entries from the database';

    public function handle(): int
    {
        $countPruned = 0;

        $this->getQuery()
            ->chunkById(500, static function (Collection $collection) use (&$countPruned) {
                $collection->toQuery()->delete();

                $countPruned += $collection->count();
            });

        $this->info("{$countPruned} entries pruned.");

        return self::SUCCESS;
    }

    protected function getQuery(): Builder
    {
        return Tracking::query()->where(
            'created_at',
            '<',
            Carbon::now()->subDays(max((int) $this->option('days'), 1)),
        );
    }
}
