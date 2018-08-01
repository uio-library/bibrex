@extends('layouts.master')

@section('content')

  {{ Form::model($library, array(
      'action' => array('LibrariesController@postStoreMyAccount'),
      'class' => 'card card-primary',
      'method' => 'post'
  )) }}

    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">Mitt bibliotek</h5>
        </div>
    </div>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">
            <div class="form-group row">
                <label for="name" class="col-sm-2 col-form-label">Norsk navn:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'name', 'value' => $library->name])
                    @endcomponent
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="name_eng" class="col-sm-2 col-form-label">Engelsk navn:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'name_eng', 'value' => $library->name_eng])
                    @endcomponent
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">E-post:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'email', 'value' => $library->email])
                    @endcomponent
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="library_code" class="col-sm-2 col-form-label">Bibliotekskode:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'library_code', 'value' => $library->library_code])
                    @endcomponent
                    <div>
                        Brukes for å utføre utlån i Alma.
                        Hvis det ikke er ønskelig at Bibrex skal brukes til å kunne utføre utlån i Alma kan feltet tømmes.
                    </div>
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="temporary_barcode" class="col-sm-2 col-form-label">Midlertidig lånekort (låne-ID):</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'temporary_barcode', 'value' => $library->temporary_barcode])
                    @endcomponent
                    <div>
                        Kort som brukes for brukere som ikke har blitt importert i Alma enda.
                        Kan stå tomt.
                    </div>
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="form-group row">
                <label for="password" class="col-sm-2 col-form-label">Passord:</label>
                <div class="col-sm-10">
                    @component('components.text', ['name' => 'password'])
                    @endcomponent
                    <p class="form-text text-muted">
                        (fyll inn kun hvis du ønsker å endre det)
                    </p>
                </div>
            </div>
        </li>

    </ul>

    <div class="card-footer">
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop

@section('scripts')

@stop
