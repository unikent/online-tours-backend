<?php

use App\Database\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateLocationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{



        foreach(array_keys(Config::get('database.connections')) as $connection) {
            $schema = DB::connection($connection)->getSchemaBuilder();

            $schema->blueprintResolver(function($table, $callback) {
                return new Blueprint($table, $callback);
            });

            $schema->create('location', function (Blueprint $table) {
                $table->increments('id');

                $table->string('name')->index();
                $table->decimal('lat', 10, 8)->index(); //-90 to +90 (degrees)
                $table->decimal('lng', 11, 8)->index(); //-180 to +180 (degrees)
                $table->text('polygon')->nullable();

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
            $schema->drop('location');
        }
	}

}
