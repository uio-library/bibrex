<?php

namespace App\Http\Controllers;

use App\Logging\DatabaseLoggingHandler;

class LogsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getIndex()
    {
        $log = new DatabaseLoggingHandler(\DB::connection());
        $items = [];

        foreach ($log->read() as $log) {
            if (is_object($log) && isset($log->time)) {
                $log->message = preg_replace_callback(
                    '/\bUser:(.+?)\b/i',
                    function ($m) {
                        return sprintf(
                            '<a href="%s">%s</a>',
                            action('UsersController@getShow', strtolower($m[1])),
                            strtolower($m[1])
                        );
                    },
                    $log->message
                );
                $items[] = $log;
            }
        }

        return response()->view('logs.index', array(
            'items' => $items
        ));
    }

    public function postDestroy()
    {
        $items = array();
        // $redis = new Predis\Client();

        // $c = $_POST['content'];
        // $ret = $redis->lrem('monolog', 1, $c);

        $ret = 0;

        if ($ret == 1) {
            return response()->json(array('success' => true));
        } else {
            return response()->json(array('success' => false));
        }
    }
}
