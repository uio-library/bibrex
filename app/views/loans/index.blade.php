@extends('layouts.master')

@section('content')

  @if (Auth::check() && is_null(Auth::user()->password))
    <div class="alert alert-danger">
      NB! Det er ikke satt noe passord for denne kontoen enda.
      <a href="/libraries/my">Gå til biblioteksinnstillinger</a>
    </div>
  @endif

  <div class="panel panel-success">

    <div class="panel-heading">
      <h3 class="panel-title">Nytt utlån</h3>
    </div>

    <div class="panel-body">

      {{ Form::model(new Loan(), array(
          'action' => 'LoansController@postStore',
          'class' => 'form-inline'
          )) }}

        <div class="row">

          <div class="col-sm-5">
            {{ Form::label('ltid', 'Til hvem?') }}
            <small>Etternavn, fornavn eller LTID</small>
            <div class="user">
                {{ Form::text('ltid', null, array(
                  'placeholder' => 'Etternavn, fornavn eller LTID', 
                  'class' => 'form-control typeahead',
                  'style' => 'display:block'
                )) }}
                {{ Form::hidden('user_id') }}
            </div>
          </div>

          <div class="col-sm-3">
            {{ Form::label('thing', 'Hva?') }}<br />
            {{ Form::select('thing', $things, null, array(
                'class' => 'form-control',
                'style' => 'display:block'
            )) }}
          </div>

          <div id="bibsysdok_extras" class="col-sm-3">
            {{ Form::label('dokid', 'DOKID:') }}<br />
            {{ Form::text('dokid', null, array(
                'placeholder' => 'DOKID',
                'class' => 'form-control',
                'style' => 'display:block'
            )) }}
          </div>

          <div id="other_extras" class="col-sm-3" style="display:none;">
            {{ Form::label('count', 'Antall:') }}<br />
            {{ Form::text('count', '1', array(
                'placeholder' => 'Antall',
                'class' => 'form-control',
                'style' => 'display:block'
            )) }}
          </div>

        </div>

      <p style="padding: 15px 0;">
        For bøker med RFID-brikker må du manuelt sette RFID-programmet i utlåns-modus for at boka skal bli avalarmisert.
        BIBREX snakker dessverre ikke med RFID-programmet (enda). 
      </p>

      {{ Form::submit('Lån ut!', array(
          'class' => 'btn btn-success'
      )) }}

      <span class="spinner" style="padding-left:10px; font-style:italic;">Et øyeblikk...</span>

      {{ Form::close() }}
    </div>

  </div>

  <div class="panel panel-success">

    <div class="panel-heading">
      <h3 class="panel-title">Utlån (<span id="loancount">{{ count($loans) }}</span>)</h3>
    </div>

    <div class="panel-body">
      Vis bare 
      <input type="checkbox" id="onlyLoansAsGuest">
        <label for="onlyLoansAsGuest">utlån på midlertidig bruker</label>
      <input type="checkbox" id="onlyOverdue">
        <label for="onlyOverdue">forfalt</label>
    </div>

    <ul class="list-group">
    @foreach ($loans as $loan)

      <li class="list-group-item{{ in_array($loan->id, $loan_ids) ? ' added' : '' }}" data-asguest="{{ $loan->as_guest ? 1 : 0 }}" data-overdue="{{ $loan->daysLeft() < 0 ? 1 : 0 }}">
        <h4 class="list-group-item-heading">
          <a href="{{ URL::action('LoansController@getShow', $loan->id) }}">
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
          {{ ($d = $loan->daysLeftFormatted()) ? "$d." : "" }}
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

    //$("select").select2();

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

    //$ltid.on('keyup', ltidChanged);
    //$ltid.on('paste', ltidChanged);   // IE, FF3  (http://stackoverflow.com/a/574971)
    //$ltid.on('input', ltidChanged);   // FF, Opera, Chrome, Safari

    var isWorking = false;
    $('form').on('submit', function(e) {
      if (isWorking) return false;

      if ($('#other_extras').is(':visible') && $('#count').val() > 3) {
        if (!confirm('Sikker på at du vil låne ut ' + $('#count').val() + ' stk.?')) {
          return false;
        }
      }
      isWorking = true;
      $('.spinner').show();
      $('input[type="button"]').prop('disabled', true);
      return true;
    });

    $('.list-group .added').addClass('focus');
    setTimeout(function() {
      $('.list-group .added').removeClass('added').removeClass('focus');
    }, 1000)

    $thing = $('select[name="thing"]');
    function thingChanged() {
      if ($thing.val() === '1') {
        $('#bibsysdok_extras').show();
        $('#other_extras').hide();
      } else {
        $('#bibsysdok_extras').hide();
        $('#other_extras').show();
      }
    }
    $thing.on('change', thingChanged);
    thingChanged();

    // $('input[name="ltid"]').typeahead([
    //   {
    //   name: 'planets',
    //   local: [ "Mercury", "Venus", "Earth", "Mars", "Jupiter", "Saturn", "Uranus", "Neptune" ]
    //   }
    //   ]);



    $('#onlyLoansAsGuest, #onlyOverdue').on('change', function() {
      var onlyLoansAsGuest = $('#onlyLoansAsGuest').is(':checked'),
        onlyOverdue = $('#onlyOverdue').is(':checked'),
        cnt = 0;
      $('.list-group-item').each(function(key, elem) {
        var c1 = (!onlyLoansAsGuest || $(elem).data('asguest')),
            c2 = (!onlyOverdue || $(elem).data('overdue'));
        if (c1 && c2) {
          cnt++;
          $(elem).show();
        } else {
          $(elem).hide();
        }
      });
      $('#loancount').text(cnt);

    });


  });
</script>

@stop
