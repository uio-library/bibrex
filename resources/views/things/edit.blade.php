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

    <p class="p-3">
      Merk: Innstillingene under gjelder for alle bibliotek. Før du endrer lånetid kan det derfor
      være lurt å diskutere med de andre brukerne.
    </p>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">
            <div class="row">
                <label for="name" class="col-sm-3 col-form-label">Internt navn:</label>
                <div class="col-sm-8">
                    @component('components.text', ['name' => 'name', 'value' => $thing->name])
                    @endcomponent
                    <p class="small form-text">
                      Dette navnet vises kun i Bibrex.
                    </p>
                </div>
            </div>
        </li>

        <li class="list-group-item">

          <div class="row mb-3">

            <div class="col col-sm-3 col-form-label">
              Bokmål:
            </div>

            <div class="col col-sm-4">
              @component('components.text', ['name' => 'name_indefinite_nob', 'value' => $thing->properties->name_indefinite->nob])
              @endcomponent
              <p class="small form-text">
                Form som passer inn i setningen «Du lånte ____ frå oss i går». Noen eksempler:
                «eit hørselvern», «ei skjøteledning», «nøkkelen til hvilerommet»
                (bestemt form fordi det bare finnes én).
              </p>
            </div>

            <div class="col col-sm-4">
              @component('components.text', ['name' => 'name_definite_nob', 'value' => $thing->properties->name_definite->nob])
              @endcomponent
              <p class="small form-text">
                Form som passer inn i setningen «____ må leveres». Noen eksempler:
                «hørselvernet», «skjøteledningen», «nøkkelen til hvilerommet».
              </p>
            </div>

          </div>


          <div class="row mb-3">

            <div class="col col-sm-3 col-form-label">
              Nynorsk:
            </div>

            <div class="col col-sm-4">
              @component('components.text', ['name' => 'name_indefinite_nno', 'value' => $thing->properties->name_indefinite->nno])
              @endcomponent
              <p class="small form-text">
                Form som passer inn i setninga «Du lånte ____ frå oss i går». Nokre eksempel:
                «eit høyrselsvern», «ei skøyteleidning», «nykelen til kvilerommet»
              </p>
            </div>

            <div class="col col-sm-4">
              @component('components.text', ['name' => 'name_definite_nno', 'value' => $thing->properties->name_definite->nno])
              @endcomponent
              <p class="small form-text">
                Form som passer inn i setninga «____ må leverast». Nokre eksempel:
                «høyrselsvernet», «skøyteleidninga», «nykelen til kvilerommet».
              </p>
            </div>

          </div>


          <div class="row mb-3">
            <div class="col col-sm-3 col-form-label">
              Engelsk:
            </div>

            <div class="col col-sm-4">
              @component('components.text', ['name' => 'name_indefinite_eng', 'value' => $thing->properties->name_indefinite->eng])
              @endcomponent
              <p class="small form-text">
                Form som passer inn i setningen «You borrowed ____ from us yesterday.
                Noen eksempler: «a pair of earmuffs», «an extension cord», «the resting room key».
              </p>
            </div>

            <div class="col col-sm-4">
              @component('components.text', ['name' => 'name_definite_eng', 'value' => $thing->properties->name_definite->eng])
              @endcomponent
              <p class="small form-text">
                Form som passer inn i setningen «____ must be returned».
                Noen eksempler:
                «the earmuffs», «the extension cord», «the resting room key».
                Første bokstav kan godt være liten for konsistens, programvaren gjør den automatisk stor ved behov.
              </p>
            </div>
          </div>

        </li>

        <li class="list-group-item">
            <div class="row">
                <label for="name" class="col-sm-3 col-form-label">Lånetid (antall dager):</label>
                <div class="col-sm-8">
                    @component('components.text', ['name' => 'loan_time', 'value' => $thing->loan_time])
                    @endcomponent
                    <p class="small form-text">
                      Minimum er «1», som innebærer at tingen purres neste morgen.
                    </p>
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <label for="note" class="col-sm-3 col-form-label">Intern merknad:</label>
                <div class="col-sm-9">
                    @component('components.text', ['name' => 'note', 'value' => $thing->note])
                    @endcomponent
                    <p class="small form-text">
                      Vises i utlånsoversikten.
                    </p>
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
