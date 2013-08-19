@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Brukere</h3>
    </div>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($users as $user)
        <li class="list-group-item">
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

  });
</script>

@stop