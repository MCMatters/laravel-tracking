<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateTrackingTable
 */
class CreateTrackingTable extends Migration
{
    /**
     * @var string
     */
    protected $table;

    /**
     * CreateRolesTable constructor.
     */
    public function __construct()
    {
        $this->table = Config::get('tracking.table');
    }

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('uri', 2000);
            $table->string('method');
            $table->json('input');
            $table->ipAddress('ip');
            $table->string('user_agent', 1000);
            $table->timestamp('created_at');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
}
