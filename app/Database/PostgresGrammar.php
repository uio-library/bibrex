<?php

namespace App\Database;

use Illuminate\Support\Fluent;

// https://stackoverflow.com/a/55023852/489916
class PostgresGrammar extends \Illuminate\Database\Schema\Grammars\PostgresGrammar
{
    /**
     * Create the column definition for a citext type.
     *
     * @param  Fluent  $column
     * @return string
     */
    protected function typeCitext(Fluent $column)
    {
        return 'citext';
    }
}
