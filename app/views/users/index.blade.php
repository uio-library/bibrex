@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Brukere</h3>
    </div>

    <p>
      <input type="checkbox" id="onlyUsersWithLoans">
      <label for="onlyUsersWithLoans">Vis bare folk med aktive lån</label>
    </p>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($users as $user)
        <li class="list-group-item" data-loanscount="{{ $user->loans->count() }}">
          <span class="badge">{{ $user->loans->count() }}</span>
          <a href="{{ URL::action('UsersController@getShow', $user->id) }}">{{ $user->lastname }}, {{ $user->firstname }}</a>
        </li>
      @endforeach
    </ul>

  </div>

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

    $('#onlyUsersWithLoans').on('change', function() {
      var onlyUsersWithLoans = $('#onlyUsersWithLoans').is(':checked');
      $('.list-group-item').each(function(key, elem) {
        if (!onlyUsersWithLoans || $(elem).data('loanscount') > 0) {
          $(elem).show();
        } else {
          $(elem).hide();
        }
      });

    });


  });
</script>

@stop