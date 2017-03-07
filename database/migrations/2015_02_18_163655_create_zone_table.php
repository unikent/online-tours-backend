<?php

use App\Database\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateZoneTable extends Migration {

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

            $schema->create('zone', function (Blueprint $table) {
                $table->integer('leaf_id')->primary();

                $table->string('name')->index();
                $table->string('slug')->unique();
                $table->integer('sequence')->unsigned()->nullable()->index();

                $table->softDeletes();

                $table->timestamps();

                $table->tracked();
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
            $schema->drop('zone');
        }
	}

}
