@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Bibliotek ({{ count($libraries) }})</h3>
    </div>

  	<div class="panel-body">
      <p>
          <a href="{{ URL::action('LibrariesController@getCreate') }}" class="btn btn-primary">Nytt bibliotek</a>
      </p>
    </div>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($libraries as $lib)
        <li class="list-group-item">
        	<h3>{{ $lib->name }}</h3>
        	Totalt {{ $lib->getLoansCount() }} utlån, {{ $lib->getActiveLoansCount() }} aktive.
        	<br />
        	<small>IP-adresser: 
		      @foreach ($lib->ips as $ip)
		      {{$ip->ip}}
		      @endforeach
            </small>
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
