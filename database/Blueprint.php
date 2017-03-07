<?php
namespace App\Database;

class Blueprint extends \Illuminate\Database\Schema\Blueprint {

    /**
     * Add created_by and updated_by fields to the table.
     *
     * @return void
     */
    public function tracked()
    {
        $this->integer('created_by')->index();

        $this->integer('updated_by')->index();
    }

    /**
     * Add creation and update timestamps to the table.
     *
     * @return void
     */
    public function timestamps()
    {
        $this->timestamp('created_at')->index();

        $this->timestamp('updated_at')->index();
    }

}