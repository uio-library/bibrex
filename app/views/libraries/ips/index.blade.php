@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">IP-adresser for autopålogging</h3>
    </div>

    <div class="panel-body">
    	<p>
    		Her konfigurerer du IP-adresser for autopålogging.
    		Sitter du på en maskin med en adresse konfigurert på
    		denne siden vil du bli automatisk logget inn.
    	</p>
    </div>

    <div class="panel-body">

      {{ Form::model(new Thing(), array(
          'action' => 'LibrariesController@storeIp',
          'class' => 'form-inline'
          )) }}

        Ny IP:
        {{ Form::text('ip', null, array(
            'placeholder' => 'IP-adresse', 
            'class' => 'form-control',
            'style' => 'width:200px'
        )) }}

        <button type="submit" class="btn btn-success">
          Legg til
        </button>

        <img src="/img/spinner2.gif" class="spinner" />

      {{ Form::close() }}

    </div>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($library->ips as $ip)
        <li class="list-group-item">
        	{{ $ip->ip }}

        	<a href="{{ URL::action('LibrariesController@removeIp', $ip->id) }}" type="submit" class="btn btn-xs btn-danger">
	          Fjern
	        </a>

        </li>
      @endforeach
    </ul>

  </div>

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
