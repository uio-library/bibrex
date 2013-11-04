@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Bruker #{{ $user->id }}</h3>
    </div>

    <div class="panel-body">

      <a href="{{ URL::action('UsersController@getEdit', $user->id) }}" style="float:right">Rediger</a>

      <table class="table">
        <tr>
          <th>
            LTID:
          </th>
          <td>
            {{ $user->ltid ? $user->ltid : '<em>Intet LTID</em>' }}
            : <span>{{ $user->in_bibsys ? 'finnes i BIBSYS' : 'finnes ikke i BIBSYS' }}</span>
            (<a href="#">Sjekk på nytt</a>)
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
      </table>

    </div>
  </div>


  <h4>Aktive utlån</h4>

  @if (count($user->loans) == 0)
    <em>Ingen utlån</em>
  @else

    <table class="table table-striped">
      <thead>
        <tr>
          <th>
            Dokument
          </th>
          <th>
            Utlånt
          </th>
          <th>
            Forfall
          </th>
        </tr>
      </thead>
      <tbody>
       @foreach ($user->loans as $loan)
        <tr>
          <td>
            <a href="{{ URL::action('LoansController@getShow', $loan->id) }}">
              {{ $loan->representation() }}
            </a>
          </td>
          <td>
            {{ $loan->created_at }}
          </td>
          <td>
            {{ $loan->daysLeftFormatted() ?: "ukjent / aldri" }}
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>  
  @endif

  <h4>Lånehistorikk</h4>

  @if (count($user->deliveredLoans) == 0)

    <p>
      <em>Ingen historikk</em>
    </p>

  @else

    <table class="table table-striped">
      <thead>
        <tr>
          <th>
            Dokument
          </th>
          <th>
            Utlånt
          </th>
          <th>
            Levert
          </th>
        </tr>
      </thead>
      <tbody>
       @foreach ($user->deliveredLoans as $loan)
        <tr>
          <td>
            <a href="{{ URL::action('LoansController@getShow', $loan->id) }}">
                {{ $loan->representation() }}
            </a>
          </td>
          <td>
            {{ $loan->created_at }}
          </td>
          <td>
            {{ $loan->deleted_at }}
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  @endif

@stop


@section('scripts')

<script type='text/javascript'>     
  $(document).ready(function() {
    $('#ltid a').on('click', function() {

      $('#ltid span').html('<img src="/img/spinner2.gif" /> ');
      
      $.get('{{ URL::action("UsersController@getNcipLookup", $user->id) }}', function(user) {
        if (user.exists) {
          $('#ltid span').html('finnes i BIBSYS');
        } else {
          $('#ltid span').html('finnes ikke i BIBSYS');
        }
      });

    });
  });
</script>

@stop