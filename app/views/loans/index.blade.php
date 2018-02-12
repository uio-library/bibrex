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

        <div style="display:flex">

          <div style="flex:1 0 auto;margin-right:1em;">
            {{ Form::label('thing', 'Hva?') }}
            <div class="thing">
                {{ Form::text('thing', null, array(
                  'placeholder' => 'Ting',
                  'class' => 'form-control typeahead',
                  'style' => 'display:block',
                  'tabindex' => '1',
                )) }}
                {{ Form::hidden('thing_id') }}
            </div>
          </div>

          <div style="flex:1 0 auto;margin-right:1em;">
            {{ Form::label('ltid', 'Til hvem?') }}
            <small>Scann kort eller skriv inn</small>
            <div class="user">
                {{ Form::text('ltid', null, array(
                  'placeholder' => 'Etternavn, fornavn eller låntaker-ID', 
                  'class' => 'form-control typeahead',
                  'style' => 'display:block;width:100%;'
                )) }}
                {{ Form::hidden('user_id') }}
            </div>
          </div>

          <div id="bibsysdok_extras" style="flex:1 0 auto; display:none;">
            {{ Form::label('dokid', 'DOKID:') }}<br />
            {{ Form::text('dokid', null, array(
                'placeholder' => 'DOKID',
                'class' => 'form-control',
                'style' => 'display:block'
            )) }}
          </div>

          <div id="other_extras" style="width:80px;margin-right:1em;">
            {{ Form::label('count', 'Antall:') }}<br />
            {{ Form::text('count', '1', array(
                'placeholder' => 'Antall',
                'class' => 'form-control',
                'style' => 'display:block'
            )) }}
          </div>
          <div>
            <div style="margin-bottom:5px;">&nbsp;</div>
            {{ Form::submit('Lån ut!', array(
                'class' => 'btn btn-success'
            )) }}
          </div>
      </div>

      <p style="padding-top:1em; float:right;">
        <a href="{{ URL::action('ThingsController@getAvailable', Auth::user()->id) }}">Oversikt over tilgjengelige ting</a> (beta)
      </p>

      <div style="padding-top:1em;">
        &nbsp;<span class="spinner" style="padding-left:5px; font-style:italic;">Et øyeblikk...</span>
      </div>

      {{ Form::close() }}
    </div>

  </div>

  <div class="panel panel-success">

    <div class="panel-heading">
      <h3 class="panel-title">Utlån (<span id="loancount">{{ count($loans) }}</span>)</h3>
    </div>

    <div class="panel-body">
      <p>Merk: Utlånshistorikk for returnerte ting anonymiseres hver natt.</p>
    </div>

    <!--<div class="panel-body">
      Vis bare 
      <input type="checkbox" id="onlyLoansAsGuest">
        <label for="onlyLoansAsGuest">utlån på gjestekort</label>
      <input type="checkbox" id="onlyOverdue">
        <label for="onlyOverdue">forfalt</label>
    </div>-->

    <ul class="list-group">
    @foreach ($loans as $loan)

      <li class="list-group-item{{ in_array($loan->id, $loan_ids) ? ' added' : '' }}"
          data-asguest="{{ $loan->as_guest ? 1 : 0 }}"
          data-overdue="{{ $loan->daysLeft() < 0 ? 1 : 0 }}"
          style="display:flex">
        <div style="flex: 1 0 auto">
          <h4 class="list-group-item-heading">
            <a href="{{ URL::action('LoansController@getShow', $loan->id) }}">
              {{ $loan->representation() }}

            </a>
            utlånt til
            <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
              {{ $loan->user->lastname }},
              {{ $loan->user->firstname }}
            </a>
            @if($loan->user->in_bibsys)
              <em class="glyphicon glyphicon-ok text-success" title="Bruker finnes i Alma"></em>
            @else
              <a href="{{ URL::action('UsersController@getNcipLookup', $loan->user->id) }}">
                <em class="glyphicon glyphicon-refresh text-warning" title="Sjekk om bruker finnes i Alma"></em>
              </a>
            @endif
          </h4>
          <p class="list-group-item-text" style="{{ $loan->relativeCreationTimeHours() > 12 ? 'font-weight: bold; color:red;' : ''; }}">
            Utlånt for {{ $loan->relativeCreationTime() }} siden.
            {{ ($d = $loan->daysLeftFormatted()) ? "$d." : "" }}
          </p>
          @if (empty($loan->user->email))
            <div class="text-danger">OBS: Ingen e-postadresse registrert!</div>
          @endif
          @foreach ($loan->reminders as $reminder)
            <div class="text-danger">
              <a class="text-danger" href="{{ URL::action('RemindersController@getShow', $reminder->id) }}">Påminnelse</a>
              sendt {{ $reminder->created_at }}
            </div>
          @endforeach
        </div>
        <div>
          <a class="btn btn-success" href="{{ URL::action('LoansController@getDestroy', $loan->id) }}?returnTo=loans.index">
            Returnér
          </a>
          <a class="btn btn-danger" href="{{ URL::action('LoansController@getLost', $loan->id) }}?returnTo=loans.index">
            Merk somt tapt
          </a>
        </div>
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

    // setTimeout(function() { $('#thing').focus(); }, 100)

    $('.spinner').hide();

    //console.info("Clearing localStorage");
    if (supports_html5_storage) {
      localStorage.clear(); // to get a fresh list of names
    }


    var users = new Bloodhound({
      prefetch: '{{ URL::to('/users') }}',
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
    });

    $('.user .typeahead')
    .typeahead({
      highlight: true,
    }, {
      name: 'brukere',
      source: users,
      display: function(item) {
        return item.value ;
      },
      templates: {
        suggestion: Handlebars.compile('<div><span class="right">{'+'{ltid}'+'}</span><span class="main">{'+'{value}'+'}</span></div>'),
      }
    })
    .on('input', function(evt, datum) {
      $('input[name="user_id"]').val('');
    })
    .on('typeahead:autocompleted', function(evt, datum) {
      $('input[name="user_id"]').val(datum.id);
      $('#count').focus();
    })
    .on('typeahead:selected', function(evt, datum) {
      $('input[name="user_id"]').val(datum.id);
      $('#count').focus();
    });


    var allThings = [
    @foreach ($things as $thing)
      {
        'id': '{{$thing->id}}',
        'value': '{{$thing->name}}',
        'avail': '{{ $thing->num_items ? $thing->availableItems() . ' tilgjengelig' : '' }}',
      },
    @endforeach
    ];
    var things = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.nonword( 'value'),
      queryTokenizer: Bloodhound.tokenizers.nonword,
      local: allThings,
    });

    function thingsWithDefaults(q, sync) {
      if (q === '') {
        sync(allThings);
      } else {
        things.search(q, sync);
      }
    }


    $('.thing .typeahead')
    .typeahead({
      minLength: 0,
      highlight: true,
    }, {
      name: 'ting',
      source: thingsWithDefaults,
      limit: 100,
      display: function(item) {
        return item.value;
      },
      templates: {
        suggestion: Handlebars.compile('<div><span class="right">{'+'{avail}'+'}</span><span class="main">{'+'{value}'+'}</span></div>'),
      }
    })
    .on('input', function(evt, datum) {
      $('input[name="thing_id"]').val('');
    })
    .on('typeahead:autocomplete', function(evt, datum) {
      $('input[name="thing_id"]').val(datum.id);
      $('#ltid').focus();
    })
    .on('typeahead:select', function(evt, datum) {
      $('input[name="thing_id"]').val(datum.id);
      $('#ltid').focus();
    });


/*
    var ltidLength = 0;
    var popovervisible = false;
    function ltidChanged(e) {
      var v = $ltid.val();
      if (v.length >= 4 && /[0-9]/.test(v) && v.substr(0,3) === 'ubo') {

        if (!popovervisible) {
          $('#ltid').attr('data-title', 'Sikker på dette?');
          $('#ltid').attr('data-content', 'Kun kortnumre som starter på «uo» importeres automatisk. «ubo»-numre må registreres manuelt med LTREG i Bibsys.');
          $('#ltid').popover('show');
        }
        popovervisible = true;
      } else if (v.length > 4 && /[0-9]/.test(v) && v.substr(0,2) !== 'uo') {
        if (!popovervisible) {
          $('#ltid').attr('data-title', 'Sikker på dette?');
          $('#ltid').attr('data-content', 'Kun kortnumre som starter på «uo» importeres automatisk. Andre numre må registreres manuelt med LTREG i Bibsys. Husk at hvis kortet er registrert ved en annen institusjon kan man bruke F12 LTKOP for å slippe å fylle inn alt.');
          $('#ltid').popover('show');
        }
        popovervisible = true;

      } else if (popovervisible) {
        $('#ltid').popover('hide');
        popovervisible = false;
      }
      // if (v.length !== ltidLength) {
      //   ltidLength = v.length;
      //   if (ltidLength == 10 && v.match('[0-9]')) {
      //     if ($('select[name="thing"]').val() === '1') {
      //       $dokid.focus();
      //     }
      //   }
      // }
    };*/

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
