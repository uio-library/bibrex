<?php

namespace App\Database;

use Illuminate\Support\Fluent;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    /**
     * Create a new citext column on the table.
     *
     * @param  string  $column
     * @return Fluent
     */
    public function citext($column)
    {
        return $this->addColumn('citext', $column);
    }
}
