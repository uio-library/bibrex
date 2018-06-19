@extends('layouts.master')

@section('content')

  <div class="card">

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
                    <a href="{{ action('ItemsController@getEdit', ['id' => '_new', 'thing' => $thing]) }}" class="btn btn-success col col-auto mx-1">
                        <i class="far fa-plus-hexagon"></i>
                        Registrer eksemplar med strekkode
                    </a>
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

        <h5>Ting</h5>



        @if ($thing->items()->whereNotNull('dokid')->first())
            <p>
                Eksemplarer av denne tingen:
            </p>
            @foreach ($thing->items as $doc)
                {{ $doc->dokid }}
            @endforeach
        @else
            <p>
                Denne tingen har ingen eksemplarer med strekkode.
            </p>
        @endif


        </div>

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

  </div>

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop
