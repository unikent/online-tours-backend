<?php

use App\Database\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateJobsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        foreach(array_keys(Config::get('database.connections')) as $connection) {
            $schema = DB::connection($connection)->getSchemaBuilder();

            $schema->blueprintResolver(function ($table, $callback) {
                return new Blueprint($table, $callback);
            });

            $schema->create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue');
                $table->text('payload');
                $table->tinyInteger('attempts')->unsigned();
                $table->tinyInteger('reserved')->unsigned();
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        foreach(array_keys(Config::get('database.connections')) as $connection) {
            $schema = DB::connection($connection)->getSchemaBuilder();
            $schema->drop('jobs');
        }
	}

}
