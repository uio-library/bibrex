<?php

namespace App\Http\Controllers;

use App\Item;
use App\Thing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Imagine\Imagick\Imagine;

class ThingsController extends Controller
{

    protected $thing;

    /**
     * Validation error messages.
     *
     * @static array
     */
    protected $messages = [
        'name.required' => 'Internt navn må fylles ut.',
        'name.unique' => 'Typen finnes allerede.',

        'loan_time.required' => 'Lånetid må fylles ut.',

        'properties.name_indefinite.nob.required' => 'Ubestemt form på bokmål må fylles ut.',
        'properties.name_definite.nob.required' => 'Bestemt form på bokmål må fylles ut.',

        'properties.name_indefinite.nno.required' => 'Ubestemt form på nynorsk må fylles ut.',
        'properties.name_definite.nno.required' => 'Bestemt form på nynorsk må fylles ut.',

        'properties.name_indefinite.eng.required' => 'Ubestemt form på engelsk må fylles ut.',
        'properties.name_definite.eng.required' => 'Bestemt form på engelsk må fylles ut.',
    ];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $libraryId = \Auth::user()->id;

        $things = Thing::with('items', 'settings', 'items.loans')
            ->orderBy('properties->name->nob')
            ->get();

        $things = $things->map(function ($thing) use ($libraryId) {
            $all = $thing->items->whereNotIn('barcode', [null]);
            $avail = $all->filter(function (Item $item) {
                return is_null($item->activeLoan);
            });
            $mine = $all->where('library_id', $libraryId);
            $avail_mine = $avail->where('library_id', $libraryId);

            return [
                'type' => 'thing',
                'id' => $thing->id,
                'name' => $thing->name(),
                'library_settings' => $thing->library_settings,
                'properties' => $thing->properties,
                'image' => $thing->image,
                'loan_time' => $thing->loan_time,

                'items_total' => $all->count(),
                'items_mine' => $mine->count(),

                'avail_total' => $avail->count(),
                'avail_mine' => $avail_mine->count(),
            ];
        });

        return response()->view('things.index', [
            'things' => $things,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function json(Request $request)
    {
        $libraryId = \Auth::user()->id;

        $things = Thing::with('settings');

        if ($request->input('withLoans')) {
            $things->with('items.loans');
        }

        $things = $things->orderBy('properties->name->nob')->get();

        if ($request->input('withoutBarcode')) {
            $things = $things->filter(function ($thing) {
                return $thing->library_settings->loans_without_barcode;
            })->values();
        }

        $things = $things->map(function ($thing) {
            return [
                'id' => $thing->id,
                'type' => 'thing',
                'name' => $thing->name(),
                'properties' => $thing->properties,
                'library_settings' => $thing->library_settings,
            ];
        });

        return response()->json($things);
    }

    /**
     * Display the specified resource.
     *
     * @param Thing $thing
     * @return Response
     */
    public function show(Thing $thing)
    {
        $items = Item::with('thing', 'library')
            ->whereNotNull('barcode')
            ->where('thing_id', '=', $thing->id)
            ->orderBy('library_id')
            ->orderBy('barcode')
            ->get();

        $stats = [
            'loans' => [
                'total' => \DB::select(
                    "select count(*) as val from loans
                      where loans.item_id IN (select id from items where thing_id=:thing_id)",
                    ['thing_id' => $thing->id]
                )[0]->val,
                'this_year' => \DB::select(
                    "select count(*) as val from loans
                      where loans.item_id in (select id from items where thing_id=:thing_id)
                      and date_part('year', loans.created_at) = :year",
                    ['thing_id' => $thing->id, 'year' => date('Y')]
                )[0]->val,
                'last_year' => \DB::select(
                    "select count(*) as val from loans
                      where loans.item_id in (select id from items where thing_id=:thing_id)
                      and date_part('year', loans.created_at) = :year",
                    ['thing_id' => $thing->id, 'year' => date('Y') - 1]
                )[0]->val,
            ]
        ];

        return response()->view('things.show', array(
            'thing' => $thing,
            'items' => $items,
            'stats' => $stats,
        ));
    }

    /**
     * Update or create the specified resource in storage.
     *
     * @param Thing $thing
     * @param Request $request
     * @return Response
     */
    public function upsert(Thing $thing, Request $request)
    {
        \Validator::make($request->all(), [
            //'name' => 'required|unique:things,name' . ($thing->id ? ',' . $thing->id : ''),

            'properties.name.nob' => 'required',
            'properties.name.nno' => 'required',
            'properties.name.eng' => 'required',

            'properties.name_indefinite.nob' => 'required',
            'properties.name_indefinite.nno' => 'required',
            'properties.name_indefinite.eng' => 'required',
            'properties.name_definite.nob' => 'required',
            'properties.name_definite.nno' => 'required',
            'properties.name_definite.eng' => 'required',
            'properties.loan_time' => 'required|numeric|min:1',
        ], $this->messages)->validate();

        $isNew = !$thing->exists;

        if ($isNew) {
            // The frontend will redirect to update the url, so flash a status message to the new page.
            \Session::flash('status', 'Tingen ble lagret.');
        }

        $thing->properties = $request->input('properties');
        $thing->save();

        if ($isNew) {
            \Log::info(sprintf(
                'Opprettet tingen <a href="%s">%s</a>.',
                action('ThingsController@show', $thing->id),
                $thing->name()
            ));
        } else {
            \Log::info(sprintf(
                'Oppdaterte tingen <a href="%s">%s</a>.',
                action('ThingsController@show', $thing->id),
                $thing->name()
            ));
        }

        return response()->json([
            'status' => 'Tingen «' . $thing->name() . '» ble lagret.',
            'thing' => $thing,
        ]);
    }

    /**
     * Update thing settings for my library.
     *
     * @param Thing $thing
     * @return Response
     */
    public function updateSettings(Thing $thing, Request $request)
    {
        \Validator::make($request->all(), [
            'loans_without_barcode' => ['required', 'boolean'],
            'reminders' => ['required', 'boolean'],
        ])->validate();

        $settings = $thing->library_settings;
        $settings->loans_without_barcode = (boolean) $request->input('loans_without_barcode');
        $settings->reminders = (boolean) $request->input('reminders');
        $settings->save();

        \Log::info(sprintf(
            'Oppdaterte innstillinger for tingen <a href="%s">%s</a>.',
            action('ThingsController@show', $thing->id),
            $thing->name()
        ));

        return response()->json([
            'status' => 'ok',
            'library_settings' => $settings,
        ]);
    }

    /**
     * Update image
     *
     * @param Thing $thing
     * @param Request $request
     * @param Imagine $imagine
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateImage(Thing $thing, Request $request, Imagine $imagine)
    {
        $this->validate($request, [
            'thingimage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
        ]);

        $file = $request->file('thingimage');
        $filename = $file->storePublicly('public');
        $filename = substr($filename, strpos($filename, '/') + 1);
        $filename_t = $filename . '.thumb.jpg';

        $fullpath  = storage_path("app/public/$filename");
        $fullpath_t = storage_path("app/public/$filename_t");

        $imagine->open($fullpath)
            ->thumbnail(
                new \Imagine\Image\Box(
                    config('bibrex.thumbnail_dimensions.width'),
                    config('bibrex.thumbnail_dimensions.height')
                ),
                \Imagine\Image\ImageInterface::THUMBNAIL_INSET
            )
            ->save($fullpath_t);

        list($width, $height) = getimagesize($fullpath);
        list($width_t, $height_t) = getimagesize($fullpath_t);

        $thing->image = [
            'full' => [
                'name' => $filename,
                'size' => filesize($fullpath),
                'width' => $width,
                'height' => $height,
            ],
            'thumb' => [
                'name' => $filename_t,
                'size' => filesize($fullpath_t),
                'width' => $width_t,
                'height' => $height_t,
            ],
        ];
        $thing->save();

        \Log::info(sprintf(
            'Oppdaterte bilde for tingen <a href="%s">%s</a>.',
            action('ThingsController@show', $thing->id),
            $thing->name()
        ));

        return response()->json([
            'status' => 'Bildet ble lagret.',
            'thing' => $thing,
        ]);
    }

    public function miscErrorResponse($msg)
    {
        return response()->json([
            'errors' => ['misc' => [$msg]],
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Thing $thing
     * @return Response
     */
    public function delete(Thing $thing)
    {
        if ($thing->items()->whereNotNull('barcode')->count() != 0) {
            return $this->miscErrorResponse('Kan ikke slette ting med eksemplarer.');
        }

        if (count($thing->activeLoans()) != 0) {
            return $this->miscErrorResponse('Kan ikke slette ting med aktive lån.');
        }

        \Log::info(sprintf(
            'Slettet tingen <a href="%s">%s</a>.',
            action('ThingsController@show', $thing->id),
            $thing->name()
        ));
        $thing->delete();

        return response()->json([
            'status' => 'Tingen «' . $thing->name() . '» ble sletta.',
            'thing' => $thing,
        ]);
    }

    /**
     * Restore the specified resource.
     *
     * @param Thing $thing
     * @return Response
     */
    public function restore(Thing $thing)
    {
        $thing->restore();
        \Log::info(sprintf(
            'Gjenopprettet tingen <a href="%s">%s</a>.',
            action('ThingsController@show', $thing->id),
            $thing->name()
        ));

        return response()->json([
            'status' => 'Tingen «' . $thing->name() . '» ble gjenopprettet.',
            'thing' => $thing,
        ]);
    }
}
