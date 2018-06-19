@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">Bibliotek ({{ count($libraries) }})</h5>
            <a href="{{ URL::action('LibrariesController@getCreate') }}" class="col col-auto mr-2 btn btn-success">
                <i class="far fa-plus-hexagon"></i>
                Nytt bibliotek
            </a>
        </div>
    </div>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($libraries as $lib)
        <li class="list-group-item">
        	<h5>
                {{ $lib->name }}
            </h5>
        	Totalt {{ $lib->getLoansCount() }} utlån, {{ $lib->getActiveLoansCount() }} aktive.
        	<br>
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
