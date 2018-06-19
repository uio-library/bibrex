@extends('layouts.master')

@section('content')

  {{ Form::model($thing, array(
      'action' => array('ThingsController@postUpdate', $thing->id ?: '_new'),
      'method' => 'post',
  )) }}

  <div class="card card-primary">

    <h5 class="card-header">
        @if (is_null($thing->id))
          Registrer ny ting
        @else
          Rediger ting
        @endif
    </h5>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">
            <div class="row">
                <label for="name" class="col-sm-3 col-form-label">Internt navn:</label>
                <div class="col-sm-9">
                    @component('components.text', ['name' => 'name', 'value' => $thing->name])
                    @endcomponent
                    <p class="form-text text-muted">
                      Dette navnet vises kun i Bibrex.
                    </p>
                </div>
            </div>
        </li>

        <li class="list-group-item">

              <h5 class="card-title">Purringer</h5>

              <div class="form-group row justify-content-end">
                <div class="col-sm-12">
                    <input type="checkbox" id="send_reminders" name="send_reminders"{{ $thing->send_reminders ? ' checked="checked"' : '' }} />
                    {{ Form::label('send_reminders', 'Send purre-eposter for denne tingen') }}
                </div>
              </div>

              <div class="form-group row">
                {{ Form::label('email_name_nob', 'Ubestemt form på bokmål: ', ['class' => 'col-sm-3 col-form-label']) }}
                <div class="col-sm-9">
                    @component('components.text', ['name' => 'email_name_nob', 'value' => $thing->email_name_nob])
                    @endcomponent
                    <p class="form-text text-muted">
                      Form som passer inn i setningen «Du lånte xxx fra oss i dag». Noen eksempler:
                      «et hørselvern», «en skjøteledning», «nøkkelen til hvilerommet».
                    </p>
                </div>
              </div>

              <div class="form-group row">
                {{ Form::label('email_name_definite_nob', 'Bestemt form på bokmål: ', ['class' => 'col-sm-3 col-form-label']) }}
                <div class="col-sm-9">
                    @component('components.text', ['name' => 'email_name_definite_nob', 'value' => $thing->email_name_definite_nob])
                    @endcomponent
                    <p class="form-text text-muted">
                      Form som passer inn i setningen «xxx må leveres». Noen eksempler:
                      «hørselvernet», «skjøteledningen», «nøkkelen til hvilerommet».
                    </p>
                </div>
              </div>

              <div class="form-group row">
                {{ Form::label('email_name_eng', 'Ubestemt form på engelsk: ', ['class' => 'col-sm-3 col-form-label']) }}
                <div class="col-sm-9">
                    @component('components.text', ['name' => 'email_name_eng', 'value' => $thing->email_name_eng])
                    @endcomponent
                    <p class="form-text text-muted">
                      Form som passer inn i setningen «You borrowed xxx from us today». Noen eksempler:
                      «a pair of earmuffs», «an extension cord», «a key to the resting room».
                    </p>
                </div>
              </div>

              <div class="form-group row">
                {{ Form::label('email_name_definite_eng', 'Bestemt form på engelsk: ', ['class' => 'col-sm-3 col-form-label']) }}
                <div class="col-sm-9">
                    @component('components.text', ['name' => 'email_name_definite_eng', 'value' => $thing->email_name_definite_eng])
                    @endcomponent
                    <p class="form-text text-muted">
                      Form som passer inn i setningen «xxx must be returned». Noen eksempler:
                      «the earmuffs», «the extension cord», «the key to the resting room».
                      Første bokstav kan godt være liten for konsistens, programvaren gjør den automatisk stor ved behov.
                    </p>
                </div>
              </div>
        </li>

        <li class="list-group-item">

              <h5 class="card-title">Forekomster</h5>

              <div class="row">
                {{ Form::label('num_items', 'Antall tilgjengelig totalt: ', ['class' => 'col-sm-3 col-form-label']) }}
                <div class="col-sm-9">
                    {{ Form::number('num_items', $thing->num_items, array('class' => 'form-control')) }}
                </div>
              </div>
        </li>

        <li class="list-group-item">
              <h5 class="card-title">Stans utlån</h5>
            <div class="row text-danger">
                <div class="col-sm-12">
                    <input type="checkbox" id="disabled" name="disabled"{{ $thing->disabled ? ' checked="checked"' : '' }} />
                    {{ Form::label('disabled', 'Ikke tillat nye utlån') }}
                </div>
              </div>
        </li>

    </ul>

    <div class="card-footer">
      <a href="{{ URL::action('ThingsController@getIndex') }}" class="btn btn-default">Avbryt</a>
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  </div>

  {{ Form::close() }}

@stop


@section('scripts')


@stop
