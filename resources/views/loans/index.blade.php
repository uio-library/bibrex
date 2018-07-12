@extends('layouts.master')

@section('content')

  @if ((new \Jenssegers\Agent\Agent() )->browser() == 'IE')

    <div class="card bg-danger text-white mb-3">
      <div class="card-body">
        Bibrex fungerer muligens i Internet Explorer,
        men bytt til Firefox eller Chrome om du opplever problemer.
      </div>
    </div>

  @endif

  @if (Auth::check() && is_null(Auth::user()->password))
    <alert variant="danger">
      NB! Det er ikke satt noe passord for denne kontoen enda.
      <a href="/libraries/my">Gå til biblioteksinnstillinger</a> for å sette det.
    </alert>
  @endif

  {{-- @if (!$has_things)
    <div class="card text-danger border-danger mb-3">
      <div class="card-body">
        <p class="card-text">
          Ingen ting er aktivert enda. Gå til <a href="/things">ting</a> for å sette opp noen ting.
        </p>
      </div>
    </div>

  @endif--}}

  <checkout-checkin :library-id="{{ $library_id }}"
                    :user="{{ json_encode($user) }}"
                    :thing="{{ json_encode($thing) }}"
  ></checkout-checkin>

@stop
