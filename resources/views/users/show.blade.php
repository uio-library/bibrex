@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <div class="card-header" >
        <div class="row align-items-center">
            <h5 class="col mb-0">
              <em class="far fa-user"></em>
              Brukerdetaljer
            </h5>

            <a href="{{ URL::action('UsersController@getEdit', $user->id) }}" class="col col-auto mx-1 btn btn-primary">
                <i class="far fa-pencil-alt"></i>
                Rediger
            </a>

              <a class="btn btn-warning col-auto mx-1 mr-3" href="{{ URL::action('UsersController@deleteForm', $user->id) }}">
                  <i class="far fa-trash"></i>
                  Slett
              </a>

        </div>
    </div>

    <div class="card-body">

        @if ($user->blocks)
            <alert variant="danger" :closable="false">
                Blokkeringsmerknader fra Alma:
                <ul>
                    @foreach ($user->blocks as $block)
                        <li>
                            {{ array_get($block, 'block_description.desc') }}
                            ({{ $block['block_note'] }}) –
                            {{ array_get($block, 'created_date') }}
                        </li>
                    @endforeach
                </ul>
            </alert>
        @endif

      <table class="table">
        <tr>
          <th>
            Navn:
          </th>
          <td>
            {{ $user->lastname }}, {{ $user->firstname }}
          </td>
        </tr>
        <tr>
          <th>
            Type:
          </th>
          <td>
            @if ($user->in_alma)
              Alma-bruker
                <a href="{{ URL::action('UsersController@sync', $user->id) }}" class="col col-auto mx-1 btn link-btn">
                    <i class="far fa-sync-alt"></i>
                    Hent oppdaterte data fra Alma
                </a>
            @else
              Lokal bruker
                <a href="{{ URL::action('UsersController@connectForm', $user->id) }}" class="col col-auto mx-1 btn btn-primary">
                    <i class="far fa-link"></i>
                    Koble med Alma-bruker
                </a>
            @endif
          </td>
        </tr>
        <tr>
          <th>
            Låne-ID:
          </th>
          <td>
            @if ($user->barcode)
              <samp>{{ $user->barcode }}</samp>
            @else
              <span class="text-danger">
                <em class="far fa-exclamation-triangle"></em>
                Mangler låne-ID
              </span>
            @endif
          </td>
        </tr>
        <tr>
          <th>
            Feide-ID:
          </th>
          <td>
            @if ($user->university_id)
              <samp>{{ $user->university_id }}</samp>
            @else
            @endif
          </td>
        </tr>
        <tr>
          <th>
            Telefon:
          </th>
          <td>
            {{ $user->phone }}
          </td>
        </tr>
        <tr>
          <th>
            Epost:
          </th>
          <td>
            {{ $user->email }}
          </td>
        </tr>
        <tr>
          <th>
            Språk:
          </th>
          <td>
            {{ $user->lang }}
          </td>
        </tr>
        <tr>
          <th>
            Registrert:
          </th>
          <td>
            {{ $user->created_at }}
          </td>
        </tr>
          <tr>
              <th>
                  Sist importert fra Alma:
              </th>
              <td>
                  {{ $user->last_import_at }}
              </td>
          </tr>
        <tr>
          <th>
            Siste aktivitet:
          </th>
          <td>
            {{ $user->last_loan_at }}
            <div>
              @if ($user->in_alma)
                <small>(Importerte brukere slettes fra Bibrex etter {{ config('bibrex.user_storage_time.imported') }} dager med inaktivitet)</small>
              @else
                <small>(Lokale brukere slettes fra Bibrex etter {{ config('bibrex.user_storage_time.local') }} dager med inaktivitet)</small>
              @endif
            </div>
          </td>
        </tr>

          <tr>
              <th>
                  Merknad:
              </th>
              <td>
                  {{ $user->note }}
              </td>
          </tr>
      </table>

    </div>
  </div>

  <div class="card my-3">

    <h5 class="card-header">
        Aktive lån
    </h5>

    <ul class="list-group list-group-flush">
        @if (count($user->loans) == 0)
            <li class="list-group-item">
                <em>Ingen lån</em>
            </li>
        @else
           @foreach ($user->loans as $loan)
            <li class="list-group-item">
                <div class="row">
                    <div class="col">
                        <a href="{{ URL::action('LoansController@getShow', $loan->id) }}">
                          {!! $loan->representation() !!}
                        </a>
                    </div>
                    <div class="col">
                        Utlånt {{ $loan->created_at }}
                    </div>
                    <div class="col">
                        Forfaller {{ $loan->due_at ?: 'aldri' }}
                    </div>
                </div>
            </li>
            @endforeach
        @endif

    </ul>
  </div>
@stop

