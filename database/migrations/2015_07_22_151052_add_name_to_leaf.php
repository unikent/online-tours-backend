<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameToLeaf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        foreach(array_keys(Config::get('database.connections')) as $connection) {
            $schema = DB::connection($connection)->getSchemaBuilder();

            $schema->blueprintResolver(function ($table, $callback) {
                return new Blueprint($table, $callback);
            });

            $schema->table('leaf', function (Blueprint $table) {
                $table->string('name');
            });
            $con = Config::get('database.connections.' . $connection);
            DB::raw('UPDATE ' .  $con['prefix'] . 'leaf set ' .  $con['prefix'] . 'leaf.name = (SELECT ' .  $con['prefix'] . 'location.name from ' .  $con['prefix'] . 'location where ' .  $con['prefix'] . 'leaf.location_id = ' .  $con['prefix'] . 'location.id) where ' .  $con['prefix'] . 'leaf.name="";');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        foreach(array_keys(Config::get('database.connections')) as $connection) {
            $schema = DB::connection($connection)->getSchemaBuilder();

            $schema->blueprintResolver(function ($table, $callback) {
                return new Blueprint($table, $callback);
            });

            $schema->table('leaf', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
}
