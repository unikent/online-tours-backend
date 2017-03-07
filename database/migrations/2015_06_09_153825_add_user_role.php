<?php
use App\Database\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AddUserRole extends Migration {

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

            $schema->table('users', function (Blueprint $table) {
                $table->string('role');

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

            $schema->blueprintResolver(function ($table, $callback) {
                return new Blueprint($table, $callback);
            });

            $schema->table('users', function (Blueprint $table) {
       	 		$table->dropColumn('role');
            });
        }
	}

}
