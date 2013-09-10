@extends('layouts.master')

@section('content')

  {{ Form::model($thing, array(
      'action' => array('ThingsController@postUpdate', $thing->id),
      'method' => 'post'
  )) }}

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Rediger ting #{{ $thing->id }}</h3>
    </div>

    <div class="panel-body">

      <div class="form-group">
        {{ Form::label('name', 'Navn: ') }}
        {{ Form::text('name', $thing->name, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        <input type="checkbox" id="disabled" name="disabled"{{ $thing->disabled ? ' checked="checked"' : '' }} />
        {{ Form::label('disabled', 'Ikke tillat nye utlån') }}
      </div>

    </div>

    <div class="panel-footer">
      <a href="{{ URL::action('ThingsController@getShow', $thing->id) }}" class="btn btn-default">Avbryt</a>
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  </div>

  {{ Form::close() }}

@stop


@section('scripts')


@stop