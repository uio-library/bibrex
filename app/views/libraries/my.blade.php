@extends('layouts.master')

@section('content')

  {{ Form::model($library, array(
      'action' => array('LibrariesController@postStoreMyAccount'),
      'class' => 'panel panel-primary',
      'method' => 'post'
  )) }}

    <div class="panel-heading">
      <h3 class="panel-title">Rediger biblioteksinnstillinger</h3>
    </div>

    <div class="panel-body">

      <div class="form-group">
        {{ Form::label('name', 'Navn') }}
        {{ Form::text('name', $library->name, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('email', 'Epost') }} (avsender for purremail)
        {{ Form::text('email', $library->email, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('guest_ltid', 'LTID for gjestekort') }} (for brukere som ikke er importert i BIBSYS)
        {{ Form::text('guest_ltid', $library->guest_ltid, array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('password', 'Nytt passord') }} (fyll inn kun hvis du ønsker å endre det)
        {{ Form::password('password', array('class' => 'form-control')) }}
      </div>

      <div class="form-group">

        {{ Form::checkbox(
          'guestcard_for_nonworking_cards',
          'true',
          array_get($library->options, 'guestcard_for_nonworking_cards') ? true : false,
          array('id' => 'guestcard_for_nonworking_cards')
        ) }}
        {{ Form::label(
          'guestcard_for_nonworking_cards',
          'Bruk gjestekort hvis brukers kort ikke virker'
        )}}
        (typisk studentkort som ikke har blitt importert enda)
      </div>

      <div class="form-group">
        {{ Form::checkbox(
          'guestcard_for_cardless_loans',
          'true',
          array_get($library->options, 'guestcard_for_cardless_loans') ? true : false,
          array('id' => 'guestcard_for_cardless_loans')
        )}}
        {{ Form::label(
          'guestcard_for_cardless_loans',
          'Bruk gjestekort for kortløse utlån'
        )}}
        (kortløse utlån kan brukes f.eks. for å slippe å opprette nytt kort til en person som kommer til å få studentkort i løpet av de nærmeste dagene.)
      </div>

    </div>

    <div class="panel-footer">
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop

@section('scripts')

@stop