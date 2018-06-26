@extends('layouts.master')

@section('content')

    {{ Form::model($user, array(
        'action' => array( 'UsersController@delete', $user->id ),
        'method' => 'post'
    )) }}

    <div class="card card-primary">

        <h5 class="card-header">
            Slette bruker?
        </h5>

        <div class="card-body">
            <p>
                Du er i ferd med å slette følgende bruker uten aktive lån: <strong>{{ $user->getName() }}</strong>.
            </p>
            <p>
                Operasjonen kan ikke angres, men hvis brukeren finnes i Alma er det enkelt å re-importere hen senere.
                Vil du fortsette?
            </p>
        </div>

        <div class="card-footer">
            <a href="{{ URL::action('UsersController@getIndex') }}" class="btn btn-secondary">Nei, jeg angrer!</a>
            {{ Form::submit('Ja, slett', array('class' => 'btn btn-danger')) }}
        </div>

    </div>

    {{ Form::close() }}

@stop
