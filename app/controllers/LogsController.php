<?php

use Dubture\Monolog\Reader\LogReader;

class LogsController extends BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{

		$reader = new LogReader(storage_path('logs/bibrex.log'));

		$items = array();

		foreach ($reader as $log) {
			if (is_array($log) && isset($log['date'])) {
				$items[] = $log;
			}
	        //echo sprintf("The log entry was written at %s. \n", $log['date']->format('Y-m-d h:i:s'));
	    }
	    $items = array_reverse($items);
		// $redis = new Predis\Client();
		// $len = $redis->llen('monolog');
		// $items = array();
		// $pattern = '/^\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>.+) (?P<context>[\[\{].*?[\]\}]) (?P<extra>[\[\{].*?[\]\}])$/sm';
		// foreach ($redis->lrange('monolog', 0, $len) as $item) {
		// 	$s = preg_match($pattern, $item, $matches);

		// 	$matches['message'] = preg_replace_callback(
		// 		'/\[\[Document:(.+?)\]\]/i',
		// 		function ($m) {
		// 			return '<a href="' . URL::action('DocumentsController@getShow', strtolower($m[1]) ) . '">' . strtolower($m[1]) . '</a>';
		// 		},
		// 		$matches['message']
		// 	);

		// 	$matches['message'] = preg_replace_callback(
		// 		'/\[\[User:(.+?)\]\]/i',
		// 		function ($m) {
		// 			return '<a href="' . URL::action('UsersController@getShow', strtolower($m[1]) ) . '">' . strtolower($m[1]) . '</a>';
		// 		},
		// 		$matches['message']
		// 	);

		// 	$items[] = array($item, $matches);
		// }
		// $items = array_reverse($items);

		return Response::view('logs.index', array(
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
			return Response::json(array('success' => true));
		} else {
			return Response::json(array('success' => false));
		}

	}

}
