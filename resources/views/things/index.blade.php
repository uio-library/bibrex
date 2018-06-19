@extends('layouts.master')

@section('content')

  <div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">Ting ({{ count($things) }})</h5>
            <a href="{{ URL::action('ThingsController@getEdit', '_new') }}" class="col col-auto mr-2 btn btn-success">
                <i class="far fa-plus-hexagon"></i>
                Legg til ting
            </a>
        </div>
    </div>

    <!-- List group -->
    <Things></Things>
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
