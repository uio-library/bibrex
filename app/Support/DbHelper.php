<?php

namespace App\Support;

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;

class DbHelper
{
    public static function isPostgres()
    {
        return is_a(\DB::connection(), PostgresConnection::class);
    }

    public static function isMysql()
    {
        return is_a(\DB::connection(), MysqlConnection::class);
    }

    public static function getDateFormat()
    {
        return \DB::connection()->getQueryGrammar()->getDateFormat();
    }
}
