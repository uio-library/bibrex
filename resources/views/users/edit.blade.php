@extends('layouts.master')

@section('content')

  {{ Form::model($user, array(
      'action' => array('UsersController@putUpdate', $user->id),
      'class' => 'card card-primary',
      'method' => 'put'
  )) }}

    <h5 class="card-header">
      Rediger bruker #{{ $user->id }}
    </h5>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">

            <div class="form-group row">
                {{ Form::label('barcode', 'Låne-ID', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    {{ Form::text('barcode', $user->barcode, array('class' => 'form-control')) }}
                    <small class="form-text text-muted">
                        Kan stå blankt hvis personen f.eks. ikke fått studiekort enda.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('university_id', 'Feide-ID', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    {{ Form::text('university_id', $user->university_id, array('class' => 'form-control')) }}
                    <small class="form-text text-muted">
                        Kan stå blankt.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('lastname', 'Etternavn', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    {{ Form::text('lastname', $user->lastname, array('class' => 'form-control')) }}
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('firstname', 'Fornavn', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    {{ Form::text('firstname', $user->firstname, array('class' => 'form-control')) }}
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('phone', 'Mobil', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    {{ Form::text('phone', $user->phone, array('class' => 'form-control')) }}
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('email', 'Epost', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    {{ Form::text('email', $user->email, array('class' => 'form-control')) }}
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('note', 'Merknad', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    {{ Form::text('note', $user->note, array('class' => 'form-control')) }}
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('lang', 'Språk', ['class' => 'col-sm-2 col-form-label']) }}
                <div class="col-sm-10">
                    {{ Form::radio('lang', 'eng', false, array('id' => 'lang-eng')) }}
                    {{ Form::label('lang-eng', 'engelsk') }}
                    {{ Form::radio('lang', 'nob', true, array('id' => 'lang-nob')) }}
                    {{ Form::label('lang-nob', 'bokmål') }}
                    {{ Form::radio('lang', 'nno', true, array('id' => 'lang-nno')) }}
                    {{ Form::label('lang-nno', 'nynorsk') }}
                </div>
            </div>

        </li>
    </ul>

    <div class="card-footer">
      <a href="{{ URL::action('UsersController@getShow', $user->id) }}" class="btn btn-default">Avbryt</a>
      <button type="submit" class="btn btn-success">
        Lagre
      </button>
    </div>

  {{ Form::close() }}

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {
    $('#ltid').focus();
  });
</script>

@stop
