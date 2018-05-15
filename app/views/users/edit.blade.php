@extends('layouts.master')

@section('content')

  {{ Form::model($user, array(
      'action' => array('UsersController@postUpdate', $user->id),
      'class' => 'panel panel-primary',
      'method' => 'post'
  )) }}

    <div class="panel-heading">
      <h3 class="panel-title">Rediger bruker #{{ $user->id }}</h3>
    </div>

    <div class="panel-body">

      <div class="form-group">
        {{ Form::label('ltid', 'LTID') }} (kan stå blankt hvis personen f.eks. ikke fått studiekort enda)
        {{ Form::text('ltid', $user->ltid, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('lastname', 'Etternavn') }}
        {{ Form::text('lastname', $user->lastname, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('firstname', 'Fornavn') }}
        {{ Form::text('firstname', $user->firstname, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('phone', 'Mobil') }}
        {{ Form::text('phone', $user->phone, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('email', 'Epost') }}
        {{ Form::text('email', $user->email, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('note', 'Merknad') }}
        {{ Form::text('note', $user->note, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::radio('lang', 'eng', false, array('id' => 'lang-eng')) }}
        {{ Form::label('lang-eng', 'engelsk') }}
        {{ Form::radio('lang', 'nor', true, array('id' => 'lang-nor')) }}
        {{ Form::label('lang-nor', 'norsk') }}
      </div>

    </div>

    <div class="panel-footer">
      <a href="{{ URL::action('UsersController@getShow', $user->id) }}" class="btn btn-default">Avbryt</a>
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop


@section('scripts')

<script type='text/javascript'>     
  $(document).ready(function() {
    $('#ltid').focus();              
  });
</script>

@stop