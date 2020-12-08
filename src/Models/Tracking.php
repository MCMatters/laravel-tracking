<?php

declare(strict_types = 1);

namespace McMatters\LaravelTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

use const false;

/**
 * Class Tracking
 *
 * @package McMatters\LaravelTracking\Models
 */
class Tracking extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $configName = 'tracking';

    /**
     * Tracking constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(Config::get("{$this->configName}.table"));
        $this->guard([$this->primaryKey]);

        parent::__construct($attributes);
    }
}
