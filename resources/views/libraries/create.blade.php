@extends('layouts.master')

@section('content')

  {{ Form::model(new App\Library(), array(
      'action' => 'LibrariesController@postStore',
      'class' => 'card card-primary form-horizontal'
      )) }}

<div class="card-header">
      <h5 class="card-title">Nytt bibliotek</h5>
    </div>

 <div class="card-body">

    <div class="form-group row">

	    <label for="name" class="col-sm-2 control-label">Norsk navn</label>
	    <div class="col-sm-10">
            @component('components.text', ['name' => 'name', 'value' => old('name')])
            @endcomponent
	    </div>
	  </div>

    <div class="form-group row">
        <label for="name_eng" class="col-sm-2 control-label">Engelsk navn</label>
        <div class="col-sm-10">
            @component('components.text', ['name' => 'name_eng', 'value' => old('name_eng')])
            @endcomponent
        </div>
    </div>

    <div class="form-group row">
	    <label for="email" class="col-sm-2 control-label">E-post </label>
	    <div class="col-sm-10">
            @component('components.text', ['name' => 'email', 'value' => old('email'), 'type' => 'email'])
            @endcomponent
	    </div>
	  </div>

         <div class="form-group row">
             <label for="library_code" class="col-sm-2 col-form-label">Bibliotekskode:</label>
             <div class="col-sm-10">
                 @component('components.text', ['name' => 'library_code', 'value' => old('library_code')])
                 @endcomponent
             </div>
         </div>

     <div class="form-group row">
	    <label for="password" class="col-sm-2 control-label">Passord </label>
	    <div class="col-sm-10">
            @component('components.text', ['name' => 'password', 'value' => old('password'), 'type' => 'password'])
            @endcomponent
        </div>
	  </div>

    <div class="form-group row">
	    <label for="password2" class="col-sm-2 control-label">Gjenta passord </label>
	    <div class="col-sm-10">
            @component('components.text', ['name' => 'password2', 'value' => old('password2'), 'type' => 'password'])
            @endcomponent
	    </div>
	  </div>

    <button type="submit" class="btn btn-success">
      Opprett
    </button>

    <img src="/img/spinner2.gif" class="spinner" />

  </div>

  {{ Form::close() }}


@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {
    $('.spinner').hide();
    $('form').on('submit', function(e) {
      $('.spinner').show();
      $('input[type="button"]').prop('disabled', true);
      return true;
    });
  });
</script>

@stop
