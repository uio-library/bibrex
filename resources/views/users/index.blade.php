@extends('layouts.master')

@section('content')

  <div class="card">

    <h5 class="card-header">
      Brukere (<span id="usercount">{{ count($users) }}</span>)
    </h5>

    <!-- List group -->
    <users-table :data="{{ json_encode($users) }}"></users-table>

  </div>

@stop
