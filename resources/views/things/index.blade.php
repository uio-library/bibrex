@extends('layouts.master')

@section('content')

@if ((new \Jenssegers\Agent\Agent() )->browser() == 'IE')


<div class="card bg-danger text-white">
  Denne siden støttes ikke i IE, ihvertfall ikke enda.
</div>

@else

  <div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">Ting ({{ count($things) }})</h5>
            <a href="{{ URL::action('ThingsController@getEdit', '_new') }}" class="col col-auto mr-2 btn btn-success">
                <i class="far fa-plus-hexagon"></i>
                Ny ting
            </a>
        </div>
    </div>

    <!-- List group -->
    <Things></Things>
  </div>

@endif

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
