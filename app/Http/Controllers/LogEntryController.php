<?php

namespace App\Http\Controllers;

use App\LogEntry;
use App\Logging\DatabaseLoggingHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Logger;

class LogEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $query = LogEntry::where('level', '>=', Logger::INFO)
            ->orderBy('id', 'desc')
            ->limit(200);

        $filters = [];

        if ($request->has('library')) {
            $query->where('context', '@>', '{"library": "' . $request->library . '"}');
            $filters[] = 'library:' . $request->library;
        }

        return response()->view('logs.index', [
            'entries' => $query->get(),
            'filters' => $filters,
        ]);
    }
}
