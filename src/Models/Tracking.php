<?php

declare(strict_types=1);

namespace McMatters\LaravelTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use const false;
use const null;

class Tracking extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'uri',
        'method',
        'input',
        'response',
        'headers',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'input' => 'json',
        'response' => 'json',
        'headers' => 'json',
        'created_at' => 'datetime',
    ];

    protected string $configName = 'tracking';

    public function __construct(array $attributes = [])
    {
        $this->setTable(Config::get("{$this->configName}.table"));
        $this->guard([$this->primaryKey]);

        parent::__construct($attributes);
    }

    protected static function booting(): void
    {
        static::creating(static function (Model $model) {
            $keyName = $model->getKeyName();
            $query = $model->newQueryWithoutScopes()->select([$keyName]);

            do {
                $uuid = (string) Str::ulid();
            } while (null !== $query->whereKey($uuid)->toBase()->first());

            $model->setAttribute($keyName, $uuid);
        });

        parent::booting();
    }
}
