@extends('layouts.master')

@section('content')

  <form method="post" action="{{ URL::action('UsersController@postMerge', array($user1->id, $user2->id)) }}" class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Flett bruker #{{ $user1->id }} og #{{ $user2->id }}</h3>
    </div>

    <div class="panel-body">

      <p>
        Se over og gjør eventuelle rettinger i brukeropplysningene før du trykker «Lagre».
        Lån fra begge brukerne vil bli samlet. Evt. lån som må overføres fra midlertidig kort
        blir ikke overført umiddelbart, men ved neste synkronisering.
      </p>

      <table class="table">

        <tr>
          <th style="width:25%;">
          </th>
          <th style="width:25%;">
            Bruker #{{ $user1->id }}
          </th>
          <th style="width:25%">
            Bruker #{{ $user2->id }}
          </th>
          <th style="width:25%">
            Flettet
          </th>
        </tr>

        <tr>
          <th>
            LTID:
          </th>
          <td>
            {{ $user1->ltid }}
            <?php
              if (!empty($user1->ltid) && !empty($user2->ltid) && ($user1->ltid != $user2->ltid)) {
                echo '<i class="halflings-icon exclamation-sign" style="font-size:16px; color:red;"></i>';
              }
            ?>
          </td>
          <td>
            {{ $user2->ltid }}
            <?php
              if (!empty($user1->ltid) && !empty($user2->ltid) && ($user1->ltid != $user2->ltid)) {
                echo '<i class="halflings-icon exclamation-sign" style="font-size:16px; color:red;"></i>';
              }
            ?>
          </td>
          <td>
            <input type="text" name="ltid" value="{{ $merged['ltid'] }}" readonly />
          </td>
        </tr>

        <tr>
          <th>
            Etternavn:
          </th>
          <td>
            {{ $user1->lastname }}
          </td>
          <td>
            {{ $user2->lastname }}
          </td>
          <td>
            <input type="text" name="lastname" value="{{ $merged['lastname'] }}" />
          </td>
        </tr>

        <tr>
          <th>
            Fornavn:
          </th>
          <td>
            {{ $user1->firstname }}
          </td>
          <td>
            {{ $user2->firstname }}
          </td>
          <td>
            <input type="text" name="firstname" value="{{ $merged['firstname'] }}" />
          </td>
        </tr>

        <tr>
          <th>
            Telefon:
          </th>
          <td>
            {{ $user1->phone }}
          </td>
          <td>
            {{ $user2->phone }}
          </td>
          <td>
            <input type="text" name="phone" value="{{ $merged['phone'] }}" />
          </td>
        </tr>

        <tr>
          <th>
            Epost:
          </th>
          <td>
            {{ $user1->email }}
          </td>
          <td>
            {{ $user2->email }}
          </td>
          <td>
            <input type="text" name="email" value="{{ $merged['email'] }}" />
          </td>
        </tr>

        <tr>
          <th>
            Språk:
          </th>
          <td>
            {{ $user1->lang }}
          </td>
          <td>
            {{ $user2->lang }}
          </td>
          <td>
            {{ Form::radio('lang', 'eng', ($merged['lang'] == 'eng' ? true : false), array('id' => 'lang-eng')) }}
            {{ Form::label('lang-eng', 'engelsk') }}
            {{ Form::radio('lang', 'nor', ($merged['lang'] == 'nor' ? true : false), array('id' => 'lang-nor')) }}
            {{ Form::label('lang-nor', 'norsk') }}
          </td>
        </tr>

      </table>

    </div>

    <div class="panel-footer">
      @if (!empty($user1->ltid) && !empty($user2->ltid) && ($user1->ltid != $user2->ltid))
        <p>
          <i class="halflings-icon exclamation-sign" style="font-size:16px; color:red;"></i> Beklager, kan ikke flette to brukere med ulike LTID.
        </p>
        <a href="{{ URL::action('UsersController@getShow', $user1->id) }}" class="btn btn-default">Avbryt</a>
        {{ Form::submit('Lagre', array('class' => 'btn btn-success', 'disabled' => 'disabled')) }}
      @else
        <a href="{{ URL::action('UsersController@getShow', $user1->id) }}" class="btn btn-default">Avbryt</a>
        {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
      @endif
    </div>

  </form>

@stop


@section('scripts')

<script type='text/javascript'>     
  $(document).ready(function() {
    $('#ltid').focus();              
  });
</script>

@stop