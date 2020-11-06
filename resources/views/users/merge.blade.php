@extends('layouts.master')

@section('content')

  <form method="post" action="{{ URL::action('UsersController@postMerge', array($user1->id, $user2->id)) }}" class="card card-primary">

    {{ csrf_field() }}

    <div class="card-header">
      <h3 class="card-title">Flett bruker #{{ $user1->id }} og #{{ $user2->id }}</h3>
    </div>

    <div class="card-body">

      <p class="mb-2">
        Se over og gjør eventuelle rettinger i brukeropplysningene før du trykker «Lagre».
        Lån fra begge brukerne vil bli samlet.
      </p>

      <table class="table table-sm">

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
            Resultat
          </th>
        </tr>

        <tr>
          <th>
            Identifikatorer:
          </th>
          <td>
            @foreach ($user1->identifiers as $identifier)
               <div>
                 {{ $identifier->value }}
               </div>
            @endforeach
          </td>
          <td>
            @foreach ($user2->identifiers as $identifier)
              <div>
                {{ $identifier->value }}
              </div>
            @endforeach
          </td>
          <td>
            <table class="table table-sm table-borderless">
              @foreach ($merged['identifiers'] as $idx => $identifier)
              <tr>
                <td>
                  <select name="identifier_type_{{ $idx }}">
                    <option value="barcode"{{ $identifier->type == 'barcode' ? ' selected="selected' : '' }}>Lånekortnummer</option>
                    <option value="university_id"{{ $identifier->type == 'university_id' ? ' selected="selected' : '' }}>Feide-ID</option>
                  </select>
                </td>
                <td>
                  <input type="text" name="identifier_value_{{ $idx }}" value="{{ $identifier->value }}" />
                </td>
              </tr>
              @endforeach
            </table>
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
            Merknader:
          </th>
          <td>
            {{ $user1->note }}
          </td>
          <td>
            {{ $user2->note }}
          </td>
          <td>
            <input type="text" name="note" value="{{ $merged['note'] }}" />
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
            {{ Form::radio('lang', 'nob', ($merged['lang'] == 'nob' ? true : false), array('id' => 'lang-nob')) }}
            {{ Form::label('lang-nob', 'bokmål') }}
            {{ Form::radio('lang', 'nno', ($merged['lang'] == 'nno' ? true : false), array('id' => 'lang-nno')) }}
            {{ Form::label('lang-nno', 'bokmål') }}
          </td>
        </tr>

      </table>

    </div>

    <div class="card-footer">
      <a href="{{ URL::action('UsersController@getShow', $user1->id) }}" class="btn btn-default">Avbryt</a>
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
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
