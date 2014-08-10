<?php

class HipChatHandler extends Monolog\Handler\HipChatHandler
{

    /**
     * Builds the body of API call
     *
     * @param  array  $record
     * @return string
     */
    private function buildContent($record)
    {
        $dataArray = array(
            'from' => $this->name,
            'room_id' => $this->room,
            'notify' => $this->notify,
            'message' => '@all ' . $record['formatted'],
            'message_format' => $this->format,
            'color' => $this->getAlertColor($record['level']),
        );

        return http_build_query($dataArray);
    }

}
