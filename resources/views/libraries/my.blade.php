@extends('layouts.master')

@section('content')

  {{ Form::model($library, array(
      'action' => array('LibrariesController@postStoreMyAccount'),
      'class' => 'card card-primary',
      'method' => 'post'
  )) }}

    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">Mitt bibliotek</h5>
        </div>
    </div>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">
            <div class="form-group row">
                <label for="name" class="col-sm-2 col-form-label">Norsk navn:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'name', 'value' => $library->name])
                    @endcomponent
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="name_eng" class="col-sm-2 col-form-label">Engelsk navn:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'name_eng', 'value' => $library->name_eng])
                    @endcomponent
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">E-post:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'email', 'value' => $library->email])
                    @endcomponent
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="guestcard_for_cardless_loans" class="col-sm-2 col-form-label">Lokale brukere:</label>
                <div class="col-sm-10">
                    {{ Form::checkbox(
                          'guestcard_for_cardless_loans',
                          'true',
                          array_get($library->options, 'guestcard_for_cardless_loans') ? true : false,
                          array('id' => 'guestcard_for_cardless_loans')
                        )}}
                        {{ Form::label(
                          'guestcard_for_cardless_loans',
                          'Opprett lokale brukere ved behov.'
                        )}}

                      <p class="text-muted">
                        Hvis søk på «Etternavn, Fornavn» gir 0 treff i Alma, tillat at det opprettes en lokal bruker.
                      </p>
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="guest_ltid" class="col-sm-2 col-form-label">Gjestekort:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'guest_ltid', 'value' => $library->guest_ltid])
                    @endcomponent
                    <p class="form-text text-muted">
                        Låne-ID for evt. gjestekort. Kan stå blankt.
                    </p>

                    <div>
                        {{ Form::checkbox(
                          'guestcard_for_nonworking_cards',
                          'true',
                          array_get($library->options, 'guestcard_for_nonworking_cards') ? true : false,
                          array('id' => 'guestcard_for_nonworking_cards')
                        ) }}
                        {{ Form::label(
                          'guestcard_for_nonworking_cards',
                          'Bruk gjestekort hvis brukers kort ikke virker'
                        )}}
                        (typisk studentkort som ikke har blitt importert enda)
                    </div>

                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="password" class="col-sm-2 col-form-label">Passord:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'password'])
                    @endcomponent
                    <p class="form-text text-muted">
                        (fyll inn kun hvis du ønsker å endre det)
                    </p>
                </div>
            </div>
        </li>

    </ul>

    <div class="card-footer">
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop

@section('scripts')

@stop
