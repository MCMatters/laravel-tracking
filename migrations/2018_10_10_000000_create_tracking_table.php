<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CreateTrackingTable extends Migration
{
    protected $table;

    public function __construct()
    {
        $this->table = Config::get('tracking.table');
    }

    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('uri', 2000);
            $table->string('method')->index();
            $table->json('input')->nullable();
            $table->json('response')->nullable();
            $table->json('headers')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 1000)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
}
