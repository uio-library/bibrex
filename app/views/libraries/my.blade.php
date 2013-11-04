@extends('layouts.master')

@section('content')

  {{ Form::model($library, array(
      'action' => array('LibrariesController@postMy'),
      'class' => 'panel panel-primary',
      'method' => 'post'
  )) }}

    <div class="panel-heading">
      <h3 class="panel-title">Rediger biblioteksinnstillinger</h3>
    </div>

    <div class="panel-body">

      <div class="form-group">
        {{ Form::label('name', 'Navn') }}
        {{ Form::text('name', $library->name, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('email', 'Epost') }} (avsender for purremail)
        {{ Form::text('email', $library->email, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('guest_ltid', 'LTID for gjestekort') }} (for brukere som ikke er importert i BIBSYS)
        {{ Form::text('guest_ltid', $library->guest_ltid, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('password', 'Nytt passord') }} (fyll inn kun hvis du ønsker å endre det)
        {{ Form::password('password', array('class' => 'form-control')) }}
      </div>

    </div>

    <div class="panel-footer">
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop

@section('scripts')

@stop