@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">Ny ting</h3>
    </div>


    @if ($e = $errors->all('<li>:message</li>'))
      <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>  
        Kunne ikke lagre fordi:
        <ul>
        @foreach ($e as $msg)
          {{$msg}}
        @endforeach
        </ul>
      </div>
    @endif

    {{ Form::model(new Thing(), array(
        'action' => 'ThingsController@postStore',
        'class' => 'form-inline'
        )) }}

      Navn:
      {{ Form::text('thing', null, array(
          'placeholder' => 'Navn', 
          'class' => 'form-control',
          'style' => 'width:120px'
      )) }}

      {{ Form::submit('Lagre ny ting', array(
          'class' => 'btn btn-success'
      )) }}

      <img src="/img/spinner2.gif" class="spinner" />

    {{ Form::close() }}

  </div>

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Ting</h3>
    </div>

    <p>
        En ting er en klasse av dokumenter.
    </p>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($things as $thing)
        <li class="list-group-item">
          {{ $thing->name }}
        </li>
      @endforeach
    </ul>

  </div>

@stop

@section('scripts')

<script type='text/javascript'>     

  $(document).ready(function() {
    $('.spinner').hide();
    $('form').on('submit', function(e) {
      $('.spinner').show();
      $('input[type="button"]').prop('disabled', true);
      return true;
    });
  });

</script>

@stop
