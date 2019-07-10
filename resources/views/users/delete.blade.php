@extends('layouts.master')

@section('content')

    {{ Form::model($user, array(
        'action' => array( 'UsersController@delete', $user->id ),
        'method' => 'post'
    )) }}

    <div class="card border-danger">

        <h5 class="card-header">
            Slette bruker?
        </h5>

        <div class="card-body">
            <p class="mb-2">
                Du er i ferd med å slette brukeren
                <a href="{{ action('UsersController@getShow', $user->id) }}"><strong>{{ $user->name }}</strong></a>.
                Brukeren har ingen aktive lån.
            </p>
            <p class="mb-2 text-danger">
                Av personvernhensyn er sletting av brukere en absolutt operasjon som ikke kan angres.
            </p>
            <p class="mb-2">
                Du kan opprette en ny bruker for den samme personen på et senere tidspunkt,
                men det blir da som en helt ny bruker.
            </p>
            <p>
                Er du sikker på at du vil fortsette?
            </p>
        </div>

        <div class="card-footer">
            <a href="{{ URL::action('UsersController@getIndex') }}" class="btn btn-secondary">Nei, jeg angrer!</a>
            {{ Form::submit('Jada, slett i vei!', array('class' => 'btn btn-danger')) }}
        </div>

    </div>

    {{ Form::close() }}

@stop
