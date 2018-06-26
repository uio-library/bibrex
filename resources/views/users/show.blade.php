@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <div class="card-header" >
        <div class="row align-items-center">
            <h5 class="col mb-0">Brukerinformasjon</h5>

            <a href="{{ URL::action('UsersController@getNcipLookup', $user->id) }}" class="col col-auto mx-1 btn btn-primary">
                <i class="far fa-sync-alt"></i>
                Re-importer fra Alma
            </a>

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

      <table class="table">
        <tr>
          <th>
            Låne-ID:
          </th>
          <td>
            @if ($user->barcode)
              <samp>{{ $user->barcode }}</samp>
            @else
              <em>Mangler</em>
            @endif
            @if ($user->barcode)
            : <span>{{ $user->in_alma ? 'finnes i Alma' : 'finnes ikke i Alma' }}</span>
            @endif
          </td>
        </tr>
        <tr>
          <th>
            Feide-ID:
          </th>
          <td>
            {!! $user->university_id ? $user->university_id : '<em>Mangler</em>' !!}
          </td>
        </tr>
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
        Aktive utlån
    </h5>

    <ul class="list-group list-group-flush">
        @if (count($user->loans) == 0)
            <li class="list-group-item">
                <em>Ingen utlån</em>
            </li>
        @else
           @foreach ($user->loans as $loan)
            <li class="list-group-item">
                <div class="row">
                    <div class="col">
                        <a href="{{ URL::action('LoansController@getShow', $loan->id) }}">
                          {{ $loan->representation() }}
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


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {
    $('#barcode a').on('click', function() {

      $('#barcode span').html('<img src="/img/spinner2.gif" /> ');

      $.get('{{ URL::action("UsersController@getNcipLookup", $user->id) }}', function(user) {
        if (user.exists) {
          $('#barcode span').html('finnes i BIBSYS');
        } else {
          $('#barcode span').html('finnes ikke i BIBSYS');
        }
      });

    });
  });
</script>

@stop
