@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Bruker #{{ $user->id }}</h3>
    </div>

    <a href="{{ URL::action('UsersController@getEdit', $user->id) }}" style="float:right">Rediger</a>

    <div class="row">
      <div class="col-2">
        <strong>LTID:</strong>
      </div>
      <div class="col-6" id="ltid">
        {{ $user->ltid ? $user->ltid : '<em>Intet LTID</em>' }}
        : <span>{{ $user->in_bibsys ? 'finnes i BIBSYS' : 'finnes ikke i BIBSYS' }}</span>
        (<a href="#">Sjekk på nytt</a>)
      </div>
    </div>

    <div class="row">
      <div class="col-2">
        <strong>Navn:</strong>
      </div>
      <div class="col-6">
        {{ $user->lastname }}, {{ $user->firstname }}
      </div>
    </div>

    <div class="row">
      <div class="col-2">
        <strong>Telefon:</strong>
      </div>
      <div class="col-6">
        {{ $user->phone }}
      </div>
    </div>

    <div class="row">
      <div class="col-2">
        <strong>Epost:</strong>
      </div>
      <div class="col-6">
        {{ $user->email }}
      </div>
    </div>

    <div class="row">
      <div class="col-2">
        <strong>Språk:</strong>
      </div>
      <div class="col-6">
        {{ $user->lang }}
      </div>
    </div>

    <div class="row">
      <div class="col-2">
        <strong>Registrert:</strong>
      </div>
      <div class="col-6">
        {{ $user->created_at }}
      </div>
    </div>
  </div>


  <h4>Utlån</h4>

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
            Levert
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
            {{ $loan->deleted_at }}
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>  
  @endif

  <h4>Lånehistorikk</h4>

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