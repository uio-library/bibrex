@extends('layouts.master')

@section('content')

    <form class="card" method="POST" action="{{ action('UsersController@connect', $user->id) }}">
        {{ csrf_field() }}

    <h5 class="card-header">
        <em class="far fa-link"></em>
        Knytt Bibrex-brukeren sammen med en Alma-bruker
    </h5>

    <p class="card-body">
        Hvis brukeren finnes i Alma kan Bibrex forsøke å koble den lokale brukeren med Alma-brukeren.
        For å få det til trengs brukerens lånekortnummer (strekkode).
        Ingen dyr blir skadet i prosessen.
    </p>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">

            <div class="form-group row">
                {{ Form::label('identifier', 'Lånekortnummer eller Feide-ID', ['class' => 'col-sm-4 col-form-label']) }}
                <div class="col-sm-8">
                    <input id="identifier" type="text" name="identifier" value="{{ old('barcode', $user_identifier) }}" class="form-control">
                </div>
            </div>

        </li>
    </ul>

    <div class="card-footer">
        @if ($user->id)
            <a href="{{ URL::action('UsersController@getShow', $user->id) }}" class="btn btn-default">Avbryt</a>
        @endif
        <button type="submit" class="btn btn-success"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
            Knytt!
        </button>
    </div>
    </form>

@stop

@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {
    $('#identifier' +
        '').focus();
  });
</script>

@stop
