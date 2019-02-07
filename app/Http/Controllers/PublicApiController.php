<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemCollection;
use App\Http\Resources\LibraryCollection;
use App\Http\Resources\ThingCollection;
use App\Item;
use App\Library;
use App\Thing;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *   title="Bibrex",
 *   version="0.1.0"
 * )
 */
class PublicApiController extends Controller
{
    protected function doc()
    {
        return response()->view('apidoc');
    }

    /**
     * @OA\Get(
     *   path="/api/libraries",
     *   summary="List libraries",
     *   description="Get a list of libraries.",
     *   tags={"Libraries"},
     *   @OA\Response(
     *     response=200,
     *     description="success"
     *   )
     * )
     *
     * @param Request $request
     * @return LibraryCollection
     */
    protected function libraries(Request $request)
    {
        $resources = Library::query()
            ->orderBy('name')
            ->get();

        return new LibraryCollection($resources);
    }

    /**
     * @OA\Get(
     *   path="/api/things",
     *   summary="List things",
     *   description="Get a list of things.",
     *   tags={"Things"},
     *   @OA\Response(
     *     response=200,
     *     description="success"
     *   ),
     *   @OA\Parameter(
     *     name="library",
     *     in="query",
     *     description="Filter by library ID.  The item counts will also reflect the selected library.",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="items",
     *     in="query",
     *     description="Include list of items for each thing.",
     *     @OA\Schema(
     *       type="boolean"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Filter by name, case-insensitive, truncate with '*'",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   )
     * )
     *
     * @param Request $request
     * @return ThingCollection
     */
    protected function things(Request $request)
    {
        $query = Thing::with('settings');

        if ($request->items && strtolower($request->items) !== 'false') {
            $query->with([
                'items' => function ($query) use ($request) {
                    if (isset($request->library)) {
                        $query->where('library_id', '=', $request->library);
                    }
                },
                'items.loans',
            ]);
            $query->with('items.library');
        }

        if (isset($request->library)) {
            $query->whereHas('items', function ($itemQuery) use ($request) {
                $itemQuery->where('library_id', '=', $request->library);
            });
        }

//        if (isset($request->name)) {
//            $query->where('name', 'ilike', str_replace('*', '%', $request->name));
//        }

        $resources = $query
            ->orderBy('properties->name->nob')
            ->get();

        return new ThingCollection($resources);
    }

    /**
     * @OA\Get(
     *   path="/api/items",
     *   summary="List items",
     *   description="Get a list of items. Only non-deleted items are returned.",
     *   tags={"Items"},
     *   @OA\Response(
     *     response=200,
     *     description="success"
     *   ),
     *   @OA\Parameter(
     *     name="library",
     *     in="query",
     *     description="Filter by library ID.",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="thing",
     *     in="query",
     *     description="Filter by thing ID.",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   )
     * )
     *
     * @param Request $request
     * @return ItemCollection
     */
    protected function items(Request $request)
    {
        $query = Item::query();

        $query->with('thing');

        if (isset($request->library)) {
            $query->where('library_id', '=', $request->library);
        }

        if (isset($request->thing)) {
            $query->where('thing_id', '=', $request->thing);
        }

        $resources = $query
            ->orderBy('barcode')
            ->get();

        return new ItemCollection($resources);
    }
}
