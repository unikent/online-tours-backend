<?php

use App\Database\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTourTable extends Migration {

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

            $schema->create('tour', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('leaf_id')->index();
                $table->string('name')->index();
                $table->text('description');
                $table->integer('sequence')->unsigned()->nullable()->index();
                $table->text('items');
                $table->integer('duration');
                $table->text('polyline');

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
            $schema->drop('tour');
        }
	}

}
