<?php

declare(strict_types=1);

namespace McMatters\LaravelTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use const false;

class Tracking extends Model
{
    public const RESPONSE_TYPE_JSON = 'json';
    public const RESPONSE_TYPE_HTML = 'html';

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

        parent::__construct($attributes);
    }

    protected static function booting(): void
    {
        static::creating(static function (Model $model) {
            $model->setAttribute($model->getKeyName(), (string) Str::ulid());
        });

        parent::booting();
    }
}
