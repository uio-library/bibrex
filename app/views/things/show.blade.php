@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Ting #{{ $thing->id }}: {{ $thing->name }}</h3>
    </div>

    <a class="btn btn-default" href="{{ URL::action('ThingsController@getEdit', $thing->id) }}">
      <i class="halflings-icon pencil"></i>
      Rediger
    </a>

    <a class="btn btn-default" href="{{ URL::action('ThingsController@getDestroy', $thing->id) }}">
      <i class="halflings-icon trash"></i>
      Slett
    </a>

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

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop