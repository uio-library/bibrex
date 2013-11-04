@extends('layouts.master')

@section('content')

  <form method="POST" action="{{ action('LibrariesController@postPassword') }}" class="panel panel-primary">

    <input type="hidden" name="password1" value="{{ Input::old('password1', $password) }}">

    <div class="panel-heading">
      <h3 class="panel-title">Nytt passord</h3>
    </div>

    <div class="panel-body">

      <p style="color:red;">
        {{ Input::old('password1', $password)? '' : 'Mistet sesjonsinformasjon' }}
      </p>

      <p style="color:red;">
        {{ Input::old('password1') ? 'Prøv igjen' : '' }}
      </p>

      <div class="form-group">
        <label for="password">Gjenta nytt passord:</label>
        <input type="password" id="password" name="password" class="form-control">
      </div>

    </div>

    <div class="panel-footer">
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop

@section('scripts')

<script type='text/javascript'>

  $(document).ready(function() {

    $('#password').focus();

  });
</script>

@stop