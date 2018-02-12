@extends('layouts.master')

@section('content')

  {{ Form::model($thing, array(
      'action' => array('ThingsController@postUpdate', $thing_id),
      'method' => 'post'
  )) }}

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">
        @if ($thing_id == '_new')
          Opprett ny ting
        @else
          Rediger ting #{{ $thing_id }}
        @endif
      </h3>
    </div>

    <div class="panel-body">

      <div class="form-group">
        {{ Form::label('name', 'Navn: ') }}
        {{ Form::text('name', $thing->name, array('class' => 'form-control')) }}
        <p class="muted">
          Dette navnet vises kun i Bibrex.
        </p>
      </div>

      <div class="form-group">
        {{ Form::label('email_name_nor', 'Purrenavn norsk ubestemt form: ') }}
        {{ Form::text('email_name_nor', $thing->email_name_nor, array('class' => 'form-control')) }}
        <p class="muted">
          Dette er navnet som vises i purre-eposter i setningen «Du lånte xxx fra oss i dag», og det
          må derfor passe inn i denne setningen. Noen eksempler:
          «et hørselvern», «en skjøteledning» og «nøkkelen til hvilerommet».
        </p>
      </div>

      <div class="form-group">
        {{ Form::label('email_name_definite_nor', 'Purrenavn norsk bestemt form: ') }}
        {{ Form::text('email_name_definite_nor', $thing->email_name_definite_nor, array('class' => 'form-control')) }}
        <p class="muted">
          Dette er navnet som vises i purre-eposter i setningen «xxx må leveres», og det
          må derfor passe inn i denne setningen. Noen eksempler:
          «hørselvernet», «skjøteledningen» og «nøkkelen til hvilerommet».
        </p>
      </div>

      <div class="form-group">
        {{ Form::label('email_name_eng', 'Purrenavn engelsk ubestemt form: ') }}
        {{ Form::text('email_name_eng', $thing->email_name_eng, array('class' => 'form-control')) }}
        <p class="muted">
          Dette er navnet som vises i purre-eposter i setningen «You borrowed xxx from us today», og det
          må derfor passe inn i denne setningen. Noen eksempler:
          «a pair of earmuffs», «an extension cord» og «a key to the resting room».
        </p>
      </div>

      <div class="form-group">
        {{ Form::label('email_name_definite_eng', 'Purrenavn engelsk bestemt form (med bestemt artikkel): ') }}
        {{ Form::text('email_name_definite_eng', $thing->email_name_definite_eng, array('class' => 'form-control')) }}
        <p class="muted">
          Dette er navnet som vises i purre-eposter i setningen «xxx must be returned», og det
          må derfor passe inn i denne setningen. Noen eksempler:
          «earmuffs», «extension cord» og «key to the resting room».
        </p>
      </div>

      <div class="form-group">
        {{ Form::label('num_items', 'Antall tilgjengelig totalt: ') }}
        {{ Form::number('num_items', $thing->num_items, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        <input type="checkbox" id="send_reminders" name="send_reminders"{{ $thing->send_reminders ? ' checked="checked"' : '' }} />
        {{ Form::label('send_reminders', 'Send purre-eposter for denne tingen') }}
      </div>

      <div class="form-group">
        <input type="checkbox" id="disabled" name="disabled"{{ $thing->disabled ? ' checked="checked"' : '' }} />
        {{ Form::label('disabled', 'Ikke tillat nye utlån') }}
      </div>

    </div>

    <div class="panel-footer">
      <a href="{{ URL::action('ThingsController@getIndex') }}" class="btn btn-default">Avbryt</a>
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  </div>

  {{ Form::close() }}

@stop


@section('scripts')


@stop