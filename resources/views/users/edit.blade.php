@extends('layouts.master')

@section('content')

    @if ($user->in_alma)

        <alert variant="warning" :closable="false">
            Endringer for brukere med Alma-kobling må gjøres i Alma.
            Men på den lyse siden: endringene blir umiddelbart reflektert i Bibrex.
        </alert>

    @endif

    <form class="card" method="POST" action="{{ action('UsersController@upsert', $user->id ?: '_new') }}">
        {{ csrf_field() }}

    <h5 class="card-header">
        @if ($user->id)
            <em class="far fa-user-edit"></em>
            Rediger bruker #{{ $user->id }}
        @else
            <em class="far fa-user-plus"></em>
            Opprett lokal bruker
        @endif
    </h5>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">

            <div class="form-group row">
                {{ Form::label('lastname', 'Etternavn', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}" class="form-control"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
                    <small class="form-text text-muted">
                        Må fylles inn.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('firstname', 'Fornavn', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}" class="form-control"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
                    <small class="form-text text-muted">
                        Må fylles inn.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('barcode', 'Låne-ID', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    <input id="barcode" type="text" name="barcode" value="{{ old('barcode', $user->barcode) }}" class="form-control"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
                    <small class="form-text text-muted">
                        Strekkoden fra lånekortet. Kan stå blankt hvis personen f.eks. ikke fått studentkort enda.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('university_id', 'Feide-ID', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    <input type="text" name="university_id" value="{{ old('university_id', $user->university_id) }}" class="form-control"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
                    <small class="form-text text-muted">
                        Feide-ID på formen brukernavn@institusjon.no. Kan stå blankt.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('email', 'Epost', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    <input type="text" name="email" value="{{ old('email', $user->email) }}" class="form-control"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
                    <small class="form-text text-muted">
                        Bør fylles ut så vi kan sende påminnelser.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('phone', 'Mobil', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
                    <small class="form-text text-muted">
                        Kan stå blankt, men greit å ha hvis vi må ringe en låner – eller hvis Bibrex
                        får støtte for SMS-påminnelser i fremtiden.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('note', 'Merknad', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    <input type="text" name="note" value="{{ old('note', $user->note) }}" class="form-control"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('lang', 'Språk', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    @foreach(['eng' => 'engelsk', 'nob' => 'bokmål', 'nno' => 'nynorsk'] as $key => $val)
                        <input id="lang-{{ $key }}" name="lang" type="radio" value="{{ $key }}"{{ old('lang', $user->lang) == $key ? ' checked="checked"' : '' }}{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
                        <label for="lang-{{ $key }}">{{ $val }}</label>
                    @endforeach
                </div>
            </div>

        </li>
    </ul>

    <div class="card-footer">
        @if ($user->id)
            <a href="{{ URL::action('UsersController@getShow', $user->id) }}" class="btn btn-default">Avbryt</a>
        @endif
        <button type="submit" class="btn btn-success"{{ $user->in_alma ? ' disabled="disabled"' : '' }}>
            Lagre
        </button>
    </div>
    </form>

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {
    $('#barcode').focus();
  });
</script>

@stop
