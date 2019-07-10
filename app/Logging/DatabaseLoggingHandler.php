<?php

namespace App\Logging;

use Illuminate\Database\Connection;
use Illuminate\Support\Arr;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DatabaseLoggingHandler extends AbstractProcessingHandler
{
    public function __construct(Connection $connection, $table = 'log', $level = Logger::DEBUG, $bubble = true)
    {
        $this->connection = $connection;
        $this->table = $table;

        parent::__construct();
    }

    public function read($level = 200, $limit = 500)
    {
        $query = $this->connection->table('log')
            ->where('level', '>=', $level)
            ->orderBy('id', 'desc')
            ->select()
            ->limit($limit);

        $rows = [];
        foreach ($query->get() as $row) {
            $row->context = json_decode($row->context, true);
            $rows[] = $row;
        }
        return $rows;
    }

    protected function write(array $record)
    {
        $data = [
            'channel'    => Arr::get($record, 'channel'),
            'message'    => Arr::get($record, 'message'),
            'level'      => Arr::get($record, 'level'),
            'level_name' => Arr::get($record, 'level_name'),
            'context'    => json_encode($record['context']),
            // 'extra'      => json_encode($record['extra']),
            'time'   => $record['datetime']->format('Y-m-d G:i:s'),
        ];

        $this->connection->table('log')->insert($data);
    }
}
