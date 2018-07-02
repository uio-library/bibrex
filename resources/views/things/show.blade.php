@extends('layouts.master')

@section('content')

  <div class="card mb-3">
    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">
                {{ $thing->name }}
            </h5>
            @if ($thing->trashed())
                <a class="btn btn-warning col col-auto mx-1" href="{{ URL::action('ThingsController@getRestore', $thing->id) }}">
                    <i class="far fa-box-full"></i>
                    Gjenopprett
                </a>
            @else
                <a class="btn btn-primary col-auto mx-1" href="{{ URL::action('ThingsController@getEdit', $thing->id) }}">
                    <i class="far fa-pencil-alt"></i>
                    Rediger
                </a>
                <a class="btn btn-warning col-auto mx-1" href="{{ URL::action('ThingsController@getDestroy', $thing->id) }}">
                    <i class="far fa-trash"></i>
                    Slett
                </a>
            @endif
        </div>
    </div>

    <ul class="list-group list-group-flush">

      <li class="list-group-item">
          <div class="row">
              <div class="col-sm-3">
                  Lånes ut i mitt bibliotek:
              </div>
              <div class="col">
                  {{ $thing->at_my_library ? 'Ja' : 'Nei' }}
              </div>
          </div>
      </li>

      <li class="list-group-item">
          <div class="row">
              <div class="col-sm-3">
                  Lånes ut m. strekkode:
              </div>
              <div class="col">
                  {{ array_get($thing->library_settings, 'require_item') ? 'Ja' : 'Nei' }}
              </div>
          </div>
      </li>

      <li class="list-group-item">
          <div class="row">
              <div class="col-sm-3">
                  Purres?
              </div>
              <div class="col">
                  {{ array_get($thing->library_settings, 'send_reminders') ? 'Ja' : 'Nei' }}
              </div>
          </div>
      </li>

      <li class="list-group-item">
          <div class="row">
              <div class="col-sm-3">
                  Merknad:
              </div>
              <div class="col">
                  {{ $thing->note ?: '–' }}
              </div>
          </div>
      </li>

      <li class="list-group-item">
          <div class="row">
              <div class="col-sm-3">
                  Lånetid (dager):
              </div>
              <div class="col">
                  {{ $thing->loan_time }}
              </div>
          </div>
      </li>
    </ul>
  </div>


  <div class="card mb-3">

    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">
                Aktive eksemplarer
            </h5>
            @if (!$thing->trashed())
                <a href="{{ action('ItemsController@editForm', ['id' => '_new', 'thing' => $thing]) }}" class="btn btn-success col col-auto mx-1">
                    <i class="far fa-plus-hexagon"></i>
                    Nytt eksemplar
                </a>
            @endif
        </div>
    </div>

    <ul class="list-group list-group-flush">
      @if ($thing->items()->whereNotNull('dokid')->count() == 0)
        <li class="list-group-item">
            <em>Ingen</em>
        </li>
      @endif
      @foreach ($thing->items()->whereNotNull('dokid')->orderBy('library_id')->orderBy('dokid')->get() as $item)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <a href="{{ action('ItemsController@show', $item->id) }}"><samp>{{ $item->dokid }}</samp></a>
            <span>{{ $item->note }}</span>
            <span>{{ $item->library->name }}</span>
        </li>
      @endforeach
    </ul>
  </div>


  <div class="card mb-3">

    <div class="card-header">
      <h5>Slettede og tapte eksemplarer</h5>
    </div>

    <ul class="list-group list-group-flush">
      @if ($thing->items()->onlyTrashed()->count() == 0)
        <li class="list-group-item">
            <em>Ingen</em>
        </li>
      @endif
      @foreach ($thing->items()->onlyTrashed()->get() as $item)
        <li class="list-group-item">
            <a href="{{ action('ItemsController@show', $item->id) }}"><samp>{{ $item->dokid }}</samp></a>
            {{ $item->is_lost ? '(tapt)' : '(slettet)' }}
            Note: {{ $item->note }}
        </li>
      @endforeach
    </ul>
  </div>

  {{--



        <div class="card-body">

        <h3>Aktive lån</h3>
        @foreach ($loans = $thing->activeLoans() as $nr => $loan)
          <hr>

          <div class="row">
            <div class="col-2">
              <strong>Låntaker:</strong>
            </div>
            <div class="col-6">
              <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
                {{ $loan->user->lastname }},
                {{ $loan->user->firstname }}
              </a>
            </div>
          </div>

          <div class="row">
            <div class="col-2">
              <strong>Utlånt:</strong>
            </div>
            <div class="col-6">
              {{ $loan->created_at }}
            </div>
          </div>

          <div class="row">
            <div class="col-2">
              <strong>Returnert:</strong>
            </div>
            <div class="col-6">
              {{ $loan->deleted_at }}
            </div>
          </div>

        @endforeach
        @if (count($loans) == 0)
          <div>
            <em>Ingen aktive lån</em>
          </div>
        @endif

        <h3>Lånehistorikk</h3>
        <ul>
        @foreach ($loans = $thing->allLoans() as $nr => $loan)

          <li style="clear:both;">
            <big class="col-1" style="float:right;">{{ (count($loans) - $nr) }}</big>
            <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
              {{ $loan->user->lastname }},
              {{ $loan->user->firstname }}
            </a><br />
            ({{ $loan->created_at }} – {{ $loan->deleted_at }})
          </li>

        @endforeach
        </ul>
        @if (count($loans) == 0)
          <div>
            <em>Ingen lånehistorikk</em>
          </div>
        @endif

    </div>

  </div>--}}



@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop
