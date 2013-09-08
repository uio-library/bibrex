@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">Ny ting</h3>
    </div>

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

      <button type="submit" class="btn btn-success">
        Lagre ny ting
      </button>

      <img src="/img/spinner2.gif" class="spinner" />

    {{ Form::close() }}

    <h3>Hva med klikkere?</h3>

    <p style="margin:10px 0;">
      Klikkere skal ikke legges til. Klikkere har HEFTID festet 
      på baksiden og lånes ut som normalt i BIBSYS. Det er per i dag 
      ikke teknisk mulig for BIBREX å låne ut dokumenter med HEFTID, 
      så hvis bruker ikke har gyldig LTID har man et problem. En 
      løsning kan være å låne ut på «Midlertid låner» (umn1002157) 
      i BIBSYS og skrive personopplysninger om låneren i 
      utlånskommentaren.
    </p>

  </div>

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Ting ({{ count($things) }})</h3>
    </div>

    <p>
        En ting er en klasse av dokumenter.
    </p>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($things as $thing)
        <li class="list-group-item">
          <a href="{{ URL::action('ThingsController@getShow', $thing->id) }}">
            {{ $thing->name }}
          </a>
          ({{ count($thing->activeLoans()) }} utlånt nå{{ $thing->disabled ? ', nye utlån tillates ikke' : '' }})
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
