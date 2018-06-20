@extends('layouts.master')

@section('content')

    {{ Form::model($item, array(
        'action' => array( 'ItemsController@postDelete', $item->id ),
        'method' => 'post'
    )) }}

    <div class="card card-primary">

        <h5 class="card-header">
            Slette eksemplar
        </h5>

        <div class="card-body">
            <p>Sikker?</p>
        </div> 

        <div class="card-footer">
            <a href="{{ URL::action('ItemsController@getIndex') }}" class="btn btn-secondary">Nei, jeg angrer!</a>
            {{ Form::submit('Ja, slett', array('class' => 'btn btn-danger')) }}
        </div>

    </div>

    {{ Form::close() }}

@stop
