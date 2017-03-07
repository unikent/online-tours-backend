<?php

use App\Database\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateLeafTable extends Migration {

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

            $schema->create('leaf', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('location_id');
                $table->string('slug', 6)->unique();

                //Baum fields for Nested Set pattern
                $table->integer('parent_id')->nullable()->index();
                $table->integer('lft')->nullable()->index();
                $table->integer('rgt')->nullable()->index();
                $table->integer('depth')->nullable()->index();

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
            $schema->drop('leaf');
        }
	}

}
