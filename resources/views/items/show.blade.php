@extends('layouts.master')

@section('content')

  <div class="card card-primary mb-3">

    <div class="card-header" >
        <div class="row align-items-center">

            @if ($item->trashed())

              <h5 class="col mb-0 text-danger">
                <i class="far fa-trash-alt"></i> <s>Eksempler #{{ $item->id }}</s> ( Dette eksemplaret er slettet)
              </h5>

              <a class="btn btn-warning col col-auto mx-1" href="{{ URL::action('ItemsController@restore', $item->id) }}">
                <i class="far fa-undo"></i>
                Gjenopprett
              </a>

            @else

              <h5 class="col mb-0">Eksempler #{{ $item->id }}</h5>

              <a href="{{ URL::action('ItemsController@editForm', $item->id) }}" class="col col-auto mx-2 btn btn-primary">
                  <i class="far fa-pencil-alt"></i>
                  Rediger
              </a>

              <a class="btn btn-warning col-auto mx-1" href="{{ URL::action('ItemsController@delete', $item->id) }}">
                  <i class="far fa-trash"></i>
                  Slett
              </a>

            @endif

        </div>
    </div>

      <ul class="list-group list-group-flush">

          <li class="list-group-item">
              <div class="row">
                  <div class="col-sm-2">
                      Ting:
                  </div>
                  <div class="col">
                      <a href="{{ URL::action('ThingsController@getShow', $item->thing->id) }}">{{ $item->thing->name }}</a>
                  </div>
              </div>
          </li>

          <li class="list-group-item">
              <div class="row">
                  <div class="col-sm-2">
                      Bibliotek:
                  </div>
                  <div class="col">
                      {{ $item->library->name }}
                  </div>
              </div>
          </li>

          <li class="list-group-item">
              <div class="row">
                  <div class="col-sm-2">
                      Strekkode:
                  </div>
                  <div class="col">
                    <samp>{{ $item->dokid ?: '(ingen)' }}</samp>
                  </div>
              </div>
          </li>

          <li class="list-group-item">
              <div class="row">
                  <div class="col-sm-2">
                      Merknad:
                  </div>
                  <div class="col">
                      {{ $item->note }}
                  </div>
              </div>
          </li>

      </ul>
  </div>

  <div class="card mb-3">

    <div class="card-header">
      <h5>Sist utlånt</h5>
    </div>

    <ul class="list-group list-group-flush">
      @if (is_null($lastLoan))
        <li class="list-group-item">
            <em>Aldri</em>
        </li>
      @else
        <li class="list-group-item">
          <a href="{{ action('LoansController@getShow', $lastLoan->id) }}">{{ $lastLoan->created_at->toDateString() }}</a>
          @if ($lastLoan->trashed())
            @if ($lastLoan->is_lost)
              <span class="text-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Markert som tapt
                {{ $lastLoan->deleted_at }}
              </span>
            @else
                returnert {{ $lastLoan->deleted_at }}
            @endif
          @else
            (ikke returnert enda)
          @endif
        </li>
      @endif
    </ul>
  </div>
@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop
