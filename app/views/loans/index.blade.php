@extends('layouts.master')

@section('content')

  <div class="panel panel-success">

    <div class="panel-heading">
      <h3 class="panel-title">Nytt utlån</h3>
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

    {{ Form::model(new Loan(), array(
        'action' => 'LoansController@postStore',
        'class' => 'form-inline'
        )) }}

      <div style="float:left; width: 260px;">
        {{ Form::label('ltid', 'Til hvem?') }}
        <small>Etternavn, fornavn eller LTID</small>
        <div class="user">
            {{ Form::text('ltid', null, array(
              'placeholder' => 'Etternavn, fornavn eller LTID', 
              'class' => 'form-control typeahead',
              'style' => 'width:250px'
            )) }}
            {{ Form::hidden('user_id') }}
        </div>
      </div>

      <div style="float:left; width: 200px;">
        {{ Form::label('thing', 'Hva?') }}<br />
        {{ Form::select('thing', $things, null, array(
            'class' => 'form-control',
            'style' => 'width:180px'
        )) }}
      </div>

      <div id="bibsysdok_extras" class="float:left; width: 310px;">
        {{ Form::label('dokid', 'DOKID:') }}<br />
        {{ Form::text('dokid', null, array(
            'placeholder' => 'DOKID',
            'class' => 'form-control',
            'style' => 'width:180px'
        )) }}
      </div>

      <div id="other_extras" style="display:none; float:left; width: 90px;">
        {{ Form::label('count', 'Antall:') }}<br />
        {{ Form::text('count', '1', array(
            'placeholder' => 'Antall',
            'class' => 'form-control',
            'style' => 'width:80px'
        )) }}
      </div>


      <p style="padding-top:1.4em; clear:both;">
        For bøker med RFID-brikker må du manuelt sette RFID-programmet i utlåns-modus for at boka skal bli avalarmisert.
        BIBREX snakker dessverre ikke med RFID-programmet (enda). 
      </p>

      {{ Form::submit('Lån ut!', array(
          'class' => 'btn btn-success'
      )) }}

      <img src="/img/spinner2.gif" class="spinner" />


    {{ Form::close() }}

  </div>

  <div class="panel panel-success">

    <div class="panel-heading">
      <h3 class="panel-title">Utlån</h3>
    </div>


    <ul class="list-group">
    @foreach ($loans as $loan)

      <li class="list-group-item{{ in_array($loan->id, $loan_ids) ? ' added' : '' }}">
        <h4 class="list-group-item-heading">
          <a href="{{ URL::action('DocumentsController@getShow', $loan->document->id) }}">
            {{ $loan->representation() }}

          </a>
          utlånt til
          <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
            {{ $loan->user->lastname }},
            {{ $loan->user->firstname }}

          </a>
        </h4>
        <p class="list-group-item-text">
          Utlånt {{ $loan->created_at }}.
          <a href="{{ URL::action('LoansController@getDestroy', $loan->id) }}?returnTo=loans.index">
            Returnér
          </a>
        </p>
      </li>
    @endforeach

    </ul>

  </div>

@stop


@section('scripts')

<script type='text/javascript'>

  var supports_html5_storage = (function () {
      try {
        return 'localStorage' in window && window['localStorage'] !== null;
      } catch (e) {
        return false;
      }
    })();

  $(document).ready(function() {
    var $ltid = $('input[name="ltid"]'),
      $dokid = $('input[name="dokid"]');

    $ltid.focus();
    $('.spinner').hide();

    //console.info("Clearing localStorage");
    if (supports_html5_storage) {
      localStorage.clear(); // to get a fresh list of names
    }

    $('.user .typeahead')
    .typeahead([{
        name: 'brukere',
        prefetch: '/users',
         template: [
            '<p class="repo-ltid">{'+'{ltid}'+'}</p><p class="repo-name">{'+'{lastname}'+'}, {'+'{firstname}'+'}</p>',
          ].join(''),
          engine: Hogan 
      }])
    .on('typeahead:autocompleted', function(evt, datum) {
      $('input[name="user_id"]').val(datum.id);
    })
    .on('typeahead:selected', function(evt, datum) {
      $('input[name="user_id"]').val(datum.id);
    })

    var ltidLength = 0;
    function ltidChanged(e) {
      var v = $ltid.val();
      if (v.length !== ltidLength) {
        ltidLength = v.length;
        if (ltidLength == 10 && v.match('[0-9]')) {
          if ($('select[name="thing"]').val() === '1') {
            $dokid.focus();
          }
        }
      }
    };

    $ltid.on('keyup', ltidChanged);
    $ltid.on('paste', ltidChanged);   // IE, FF3  (http://stackoverflow.com/a/574971)
    $ltid.on('input', ltidChanged);   // FF, Opera, Chrome, Safari

    var isWorking = false;
    $('form').on('submit', function(e) {
      if (isWorking) return false;
      isWorking = true;
      $('.spinner').show();
      $('input[type="button"]').prop('disabled', true);
      return true;
    });

    $('.list-group .added').addClass('focus');
    setTimeout(function() {
      $('.list-group .added').removeClass('added').removeClass('focus');
    }, 1000)

    $('select[name="thing"]').on('change', function(e) {
      if ($(e.target).val() === '1') {
        $('#bibsysdok_extras').show();
        $('#other_extras').hide();
      } else {
        $('#bibsysdok_extras').hide();
        $('#other_extras').show();       
      }
    });

    // $('input[name="ltid"]').typeahead([
    //   {
    //   name: 'planets',
    //   local: [ "Mercury", "Venus", "Earth", "Mars", "Jupiter", "Saturn", "Uranus", "Neptune" ]
    //   }
    //   ]);

  });
</script>

@stop