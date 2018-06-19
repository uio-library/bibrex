@extends('layouts.master')

@section('content')

  <form method="POST" action="{{ action('LibrariesController@postPassword') }}" class="card card-primary">

    <input type="hidden" name="password1" value="{{ old('password1', $password) }}">

    <div class="card-header">
      <h3 class="card-title">Nytt passord</h3>
    </div>

    <div class="card-body">

      <p style="color:red;">
        {{ old('password1', $password)? '' : 'Mistet sesjonsinformasjon' }}
      </p>

      <p style="color:red;">
        {{ old('password1') ? 'Prøv igjen' : '' }}
      </p>

      <div class="form-group">
        <label for="password">Gjenta nytt passord:</label>
        <input type="password" id="password" name="password" class="form-control">
      </div>

    </div>

    <div class="card-footer">
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
