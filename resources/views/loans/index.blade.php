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

    <div id="maincp" style="display: none;">


      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link{{ $tab == 'default' ? ' active' : '' }}" id="nav-checkout-tab" data-toggle="tab" href="#nav-checkout" role="tab">Utlån</a>
        </li>
        <li class="nav-item">
          <a class="nav-link{{ $tab == 'retur' ? ' active' : '' }}" id="nav-checkin-tab" data-toggle="tab" href="#nav-checkin" role="tab">Retur</a>
        </li>
      </ul>
      <div class="tab-content p-3 mb-3">
        <div class="tab-pane fade show{{ $tab == 'default' ? ' active' : '' }}" id="nav-checkout" role="tabpanel" aria-labelledby="nav-checkout-tab">
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

              <div class="col px-2">
                <label>&nbsp;</label>
                <button class="btn btn-success checkout" type="submit" style="display: block; width: 100%;" tabindex="4">
                    <i class="far fa-paper-plane"></i>

                    <div class="lds-ellipsis spinner"><div></div><div></div><div></div><div></div></div>
                    <span class="btntext">Lån ut</span>

                </button>
              </div>
          {{ Form::close() }}

        </div>

        <div class="tab-pane fade show{{ $tab == 'retur' ? ' active' : '' }}" id="nav-checkin" role="tabpanel" aria-labelledby="nav-checkin-tab">

          {{ Form::model(new App\Loan(), array(
                'action' => 'LoansController@postDestroy',
                'class' => 'form row px-2'
                )) }}

                <div class="col-sm-8 px-2">
                  <label for="barcode">Strekkode:</label>
                  <input class="form-control" tabindex="1" type="text" name="barcode" autocomplete="off">
                  <small class="form-text text-muted">
                    &nbsp;
                  </small>
                </div>

                <div class="col px-2">
                  <label>&nbsp;</label>
                  <button class="btn btn-success checkout" type="submit" style="display: block; width: 100%;" tabindex="4">
                      <div class="lds-ellipsis spinner"><div></div><div></div><div></div><div></div></div>
                      <span class="btntext">Returner</span>
                  </button>
                </div>
            {{ Form::close() }}

        </div>
      </div>
    </div>

  @endif

  <table id="myTable" class="table" style="display: none; width:100%">
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
              <div class="btn-group btn-group" role="group">
                  <a title="Returnér tingen" class="btn btn-success" href="{{ URL::action('LoansController@getDestroy', $loan->id) }}?returnTo=loans.index">
                      Retur
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
@stop

@section('scripts')

  <script type='text/javascript'>

    // -- CheckoutCheckin

    function focusFirstTextInput() {
      var inp = document.querySelector('.active input[tabindex="1"]');
      inp.focus();
      return inp;
    }

    document.querySelectorAll('.nav-tabs a').forEach(function(node) {
      node.addEventListener('click', function() {
        setTimeout(focusFirstTextInput, 300);
      });
    });

    window.addEventListener('keypress', function (evt) {
      if (evt.altKey || evt.ctrlKey || evt.metaKey) return;
      if (evt.target == document.body && evt.key) {
        setTimeout(function () {
          var inp = focusFirstTextInput();
          inp.value += evt.key;
        });
      }
    });

    $('.added').addClass('focus');
    setTimeout(function() {
      $('.added').removeClass('added').removeClass('focus');
    }, 3000);

    $('#maincp').show();
    $('.spinner').hide();
    $('.btntext').show();
    var isWorking = false;
    $('form').on('submit', function(e) {
      if (isWorking) return false;
      isWorking = true;
      $('.spinner').show();
      $('.btntext').hide();
      $('.checkout').prop('disabled', true);
      return true;
    });

    // -- Datatable

    $('#myTable').show();

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
