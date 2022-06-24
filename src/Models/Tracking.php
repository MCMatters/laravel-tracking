<?php

declare(strict_types=1);

namespace McMatters\LaravelTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use const false;
use const null;

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
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'uri',
        'method',
        'input',
        'headers',
        'ip',
        'user_agent',
        'created_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'input' => 'json',
        'headers' => 'json',
        'created_at' => 'datetime',
    ];

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

        static::creating(static function (Model $model) {
            $keyName = $model->getKeyName();
            $query = $model->newQueryWithoutScopes()->select([$keyName]);

            do {
                $uuid = Str::uuid()->toString();
            } while (null !== $query->whereKey($uuid)->toBase()->first());

            $model->setAttribute($keyName, $uuid);
        });

        parent::__construct($attributes);
    }
}
