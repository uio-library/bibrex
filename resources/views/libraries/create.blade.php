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
	      {{ Form::text('name', null, array(
	      	'id' => 'name',
            'class' => 'form-control'
        )) }}
	    </div>
	  </div>

    <div class="form-group row">
        <label for="name" class="col-sm-2 control-label">Engelsk navn</label>
        <div class="col-sm-10">
          {{ Form::text('name_eng', null, array(
            'id' => 'name_eng',
            'class' => 'form-control'
        )) }}
        </div>
    </div>

    <div class="form-group row">
	    <label for="email" class="col-sm-2 control-label">E-post </label>
	    <div class="col-sm-10">
	      {{ Form::text('email', null, array(
	      	'id' => 'email',
            'class' => 'form-control'
        )) }}
	    </div>
	  </div>

    <div class="form-group row">
	    <label for="password" class="col-sm-2 control-label">Passord </label>
	    <div class="col-sm-10">
	      {{ Form::password('password',  array(
	      	'id' => 'password',
            'class' => 'form-control'
        )) }}
	    </div>
	  </div>

    <div class="form-group row">
	    <label for="password2" class="col-sm-2 control-label">Gjenta passord </label>
	    <div class="col-sm-10">
	      {{ Form::password('password2', array(
	      	'id' => 'password2',
            'class' => 'form-control'
        )) }}
	    </div>
	  </div>

    <button type="submit" class="btn btn-success">
      Lagre bibliotek
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
