@extends('layouts.master')

@section('content')

@if ((new \Jenssegers\Agent\Agent() )->browser() == 'IE')

  <div class="card bg-danger text-white mb-3">
  <div class="card-body">
    Bibrex fungerer til en viss grad i Internet Explorer,
    men bruk helst heller Firefox eller Chrome, ihvertfall inntil videre.
  </div>
  </div>


@endif



  @if (Auth::check() && is_null(Auth::user()->password))
    <div class="alert alert-danger">
      NB! Det er ikke satt noe passord for denne kontoen enda.
      <a href="/libraries/my">Gå til biblioteksinnstillinger</a>
    </div>
  @endif

    @if (!$has_things)

<div class="card text-danger border-danger mb-3">
<div class="card-body">
  <p class="card-text">
    Ingen ting er aktivert enda. Gå til <a href="/things">ting</a> for å sette opp noen ting.
  </p>
</div>
</div>

    @else


  <div class="card mb-5">

    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">Nytt utlån</h5>

            <div class="col col-auto">
                <div class="lds-ellipsis spinner"><div></div><div></div><div></div><div></div></div>
            </div>
        </div>
    </div>

    <div class="card-body">

      {{ Form::model(new App\Loan(), array(
          'action' => 'LoansController@postStore',
          'class' => 'form row px-2'
          )) }}


          <div class="col-sm-5 px-2">
            <label for="user">Til hvem?</label>
            <typeahead name="user" :tabindex="1" value="{{ old('user') }}" prefetch="/users" remote="/users/search-alma" placeholder="Til hvem?" :min-length="4" :alma="true"></typeahead>
            <small class="form-text text-muted">
                Navn eller låne-ID
            </small>
          </div>


          <div class="col-sm-5 px-2">
            <label for="thing">Hva?</label>
            <typeahead name="thing" :tabindex="2" value="{{ old('thing') }}" prefetch="/things?mine=1" remote="/items/search" :min-length="0" :limit="30"></typeahead>
            <small class="form-text text-muted">
              Scann eller velg ting
            </small>
          </div>


<!--
          <div id="bibsysdok_extras" style="flex:1 0 auto; display:none;">
            {{ Form::label('dokid', 'DOKID:') }}<br />
            {{ Form::text('dokid', null, array(
                'placeholder' => 'DOKID',
                'class' => 'form-control',
                'style' => 'display:block'
            )) }}
          </div>
--><!--
          <div id="other_extras" class="col px-2">
            <label for="count">Antall:</label>
            {{ Form::text('count', '1', array(
                'placeholder' => 'Antall',
                'class' => 'form-control',
                'style' => 'display:block; width:100%;',
                'tabindex' => '3',
            )) }}
          </div>-->

          <div class="col px-2">
            <label>&nbsp;</label>
            <button class="btn btn-success checkout" type="submit" style="display: block; width: 100%;" tabindex="4">
                <i class="far fa-paper-plane"></i>
                Lån ut!
            </button>
          </div>
      {{ Form::close() }}

      {{--
      <p style="padding-top:1em; float:right;">
        <a href="{{ URL::action('ThingsController@getAvailable', Auth::user()->id) }}">Oversikt over tilgjengelige ting</a> (beta)
      </p>

      <div style="padding-top:1em; display: none;">
        &nbsp;<span class="spinner" style="padding-left:5px; font-style:italic;">Et øyeblikk...</span>
      </div>
      --}}

    </div>

  </div>
  @endif



    <!--<div class="card-body">
      Vis bare
      <input type="checkbox" id="onlyLoansAsGuest">
        <label for="onlyLoansAsGuest">utlån på gjestekort</label>
      <input type="checkbox" id="onlyOverdue">
        <label for="onlyOverdue">forfalt</label>
    </div>-->

    <table id="myTable" class="table" style="width:100%">
        <thead>
            <th>Lån</th>
            <th>Bruker</th>
            <th>Utlånt</th>
            <th>Forfall</th>
            <th>Merknader</th>
            <th>Knapper</th>
        </thead>
        <tbody>
            @foreach ($loans as $loan)
            <tr class="{{ in_array($loan->id, $loan_ids) ? 'added' : '' }}">
                <td>
                    <a href="{{ URL::action('LoansController@getShow', $loan->id) }}">{{ $loan->item->thing->name }}</a>
                    @if ($loan->item->dokid)
                        (<samp>{{ $loan->item->dokid }}</samp>)
                    @endif
                </td>
                <td data-order="{{ $loan->user->lastname }}, {{ $loan->user->firstname }}">
                    @if($loan->user->in_alma)
                        <i class="fa fa-user-check text-success" title="Importert fra Alma"></i>
                    @elseif ($loan->user->barcode)
                        <a href="{{ URL::action('UsersController@getNcipLookup', $loan->user->id) }}" title="Prøv å importere brukeropplysninger fra Alma">
                            <i class="fa fa-user-times text-warning"></i>
                        </a>
                    @else
                        <i class="fa fa-user-times text-danger"></i>
                    @endif

                    <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
                        {{ $loan->user->lastname }},
                        {{ $loan->user->firstname }}
                    </a>
                </td>

                <td data-order="{{ $loan->created_at }}">
                    @if (in_array($loan->id, $loan_ids))
                        <em>nå nettopp</em>
                    @else
                        {{ $loan->relativeCreationTime() }}
                    @endif
                </td>
                <td data-order="{{ $loan->due_at }}">
                    <a title="Rediger forfallsdato" href="{{ action('LoansController@edit', $loan->id) }}">
                      {!! $loan->daysLeftFormatted() !!}
                      <i class="far fa-pencil"></i>
                    </a>
                </td>


                <td>
                    @if (empty($loan->user->barcode))
                        <div class="text-danger">
                            <em class="fas fa-exclamation-triangle"></em>
                            OBS: Ingen låne-ID registrert på brukeren!
                        </div>
                    @endif
                    @if (empty($loan->user->email))
                        <div class="text-danger">
                            <em class="fas fa-exclamation-triangle"></em>
                            OBS: Ingen e-postadresse registrert på brukeren!
                        </div>
                    @endif
                    @if ($loan->user->note)
                        <div class="text-info">
                            <i class="far fa-comment"></i>
                            {{ $loan->user->note }}
                        </div>
                    @endif
                    @if ($loan->note)
                        <div class="text-info" v-b-tooltip.hover title="Merknad på lånet">
                            <i class="far fa-comment"></i>
                            {{ $loan->note }}
                        </div>
                    @endif
                    @if ($loan->item->note)
                        <div class="text-info" v-b-tooltip.hover title="Merknad på eksemplaret">
                            <i class="far fa-comment"></i>
                            {{ $loan->item->note }}
                        </div>
                    @endif
                    @if ($loan->item->thing->note)
                        <div class="text-info" v-b-tooltip.hover title="Merknad på tingen">
                            <i class="far fa-comment"></i>
                            {{ $loan->item->thing->note }}
                        </div>
                    @endif
                    @foreach ($loan->reminders as $reminder)
                        <div class="text-danger">
                            <a class="text-danger" href="{{ URL::action('RemindersController@getShow', $reminder->id) }}">
                                <em class="glyphicon glyphicon-envelope text-danger"></em>
                                Påminnelse</a>
                            ble sendt {{ $reminder->created_at }}.
                        </div>
                    @endforeach
                </td>

                <td>
                    <div style="flex: 0 0 auto">
                        <a title="Returnér tingen" class="btn btn-success" href="{{ URL::action('LoansController@getDestroy', $loan->id) }}?returnTo=loans.index">
                            Returnér
                        </a>
                        <a title="Merk som tapt" class="btn btn-danger" href="{{ URL::action('LoansController@getLost', $loan->id) }}?returnTo=loans.index">
                            Tapt
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="text-muted">Merk: Utlånshistorikk for returnerte ting anonymiseres hver natt.</p>

    {{--
    <ul class="list-group list-group-flush">
    @foreach ($loans as $loan)

      <li class="list-group-item{{ in_array($loan->id, $loan_ids) ? ' added' : '' }}"
          data-asguest="{{ $loan->as_guest ? 1 : 0 }}"
          data-overdue="{{ $loan->daysLeft() < 0 ? 1 : 0 }}">
        <div class="row px-3">
            <div style="flex: 1 0 auto">
              <h5 class="list-group-item-heading mb-0">
                <a href="{{ URL::action('LoansController@getShow', $loan->id) }}">{!! $loan->representation() !!}</a>
                utlånt til
                <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
                  {{ $loan->user->lastname }},
                  {{ $loan->user->firstname }}
                </a>
                @if($loan->user->in_alma)
                  <em class="glyphicon glyphicon-ok text-success" title="Bruker finnes i Alma"></em>
                @else
                  <a href="{{ URL::action('UsersController@getNcipLookup', $loan->user->id) }}">
                      <em class="glyphicon glyphicon-refresh text-warning" title="Prøv å importere brukeropplysninger fra Alma"></em>
                  </a>
                @endif
              </h5>
              <p class="list-group-item-text {{ $loan->relativeCreationTimeHours() > 12 ? 'text-danger' : '' }}">
                Utlånt {{ $loan->relativeCreationTime() }}.
                {{ ($d = $loan->daysLeftFormatted()) ? "$d." : "" }}
              </p>
              @if (empty($loan->user->email))
                    <div class="text-danger">
                        <em class="fas fa-exclamation-triangle"></em>
                        OBS: Ingen e-postadresse registrert på brukeren!
                    </div>
                @endif
                @if ($loan->user->note)
                    <div>
                        <em class="glyphicon glyphicon-user"></em>
                        {{ $loan->user->note }}
                    </div>
              @endif
              @foreach ($loan->reminders as $reminder)
                <div class="text-danger">
                    <a class="text-danger" href="{{ URL::action('RemindersController@getShow', $reminder->id) }}">
                        <em class="glyphicon glyphicon-envelope text-danger"></em>
                        Påminnelse</a> ble sendt {{ $reminder->created_at }}.
                </div>
              @endforeach
            </div>
            <div style="flex: 0 0 auto">
              <a class="btn btn-success" href="{{ URL::action('LoansController@getDestroy', $loan->id) }}?returnTo=loans.index">
                Returnér
              </a>
              <a class="btn btn-danger" href="{{ URL::action('LoansController@getLost', $loan->id) }}?returnTo=loans.index">
                Merk somt tapt
              </a>
            </div>
        </div>
      </li>
    @endforeach

    </ul>
    --}}


@stop


@section('scripts')

<script type='text/javascript'>




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

/*
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

    $('#myTable').DataTable({
        order: [[ 2, "desc" ]]
    });

  });*/


  window.addEventListener('keypress', evt => {
    if (evt.altKey || evt.ctrlKey || evt.metaKey) return;
    if (evt.target == document.body && evt.key) {
        setTimeout(() => {
          let inp = document.querySelector('input[tabindex="1"]');
          inp.value += evt.key;
          inp.focus();
        });
      }
    });


    $('.added').addClass('focus');
    setTimeout(function() {
      $('.added').removeClass('added').removeClass('focus');
    }, 3000);


    $('.spinner').hide();

    var isWorking = false;
    $('form').on('submit', function(e) {
      if (isWorking) return false;

      isWorking = true;
      $('.spinner').show();
      $('.checkout').prop('disabled', true);
      return true;
    });

    $('#myTable').DataTable({
        order: [[ 2, "desc" ]],

        paging: false,
        info: false,

        // Source: https://datatables.net/plug-ins/i18n/Norwegian-Bokmal
        language: {
            "sEmptyTable": "Ingen data tilgjengelig i tabellen",
            "sInfo": "Viser _START_ til _END_ av _TOTAL_ linjer",
            "sInfoEmpty": "Viser 0 til 0 av 0 linjer",
            "sInfoFiltered": "(filtrert fra _MAX_ totalt antall linjer)",
            "sInfoPostFix": "",
            "sInfoThousands": " ",
            "sLoadingRecords": "Laster...",
            "sLengthMenu": "Vis _MENU_ eksemplarer",
            "sLoadingRecords": "Laster...",
            "sProcessing": "Laster...",
            "sSearch": "S&oslash;k:",
            "sUrl": "",
            "sZeroRecords": "Ingen linjer matcher s&oslash;ket",
            "oPaginate": {
                "sFirst": "F&oslash;rste",
                "sPrevious": "Forrige",
                "sNext": "Neste",
                "sLast": "Siste"
            },
            "oAria": {
                "sSortAscending": ": aktiver for å sortere kolonnen stigende",
                "sSortDescending": ": aktiver for å sortere kolonnen synkende"
            }
          },

    });
</script>

@stop
