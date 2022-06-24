<?php

declare(strict_types=1);

namespace McMatters\LaravelTracking\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use McMatters\LaravelTracking\Models\Tracking;

/**
 * Class PruneCommand
 *
 * @package McMatters\LaravelTracking\Console\Commands
 */
class PruneCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tracking:prune {--days=30: The number of days to retain data}';

    /**
     * @var string
     */
    protected $description = 'Prune stale entries from the database';

    /**
     * @return int
     */
    public function handle(): int
    {
        $count = $this->getQuery()->delete();

        $this->info("{$count} entries pruned.");

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery(): Builder
    {
        $days = $this->option('days');

        return Tracking::query()->where(
            'created_at',
            '<',
            Carbon::parse("-{$days} days"),
        );
    }
}
