@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <h5 class="card-header">
      Eksemplarer ({{ count($items) }})
    </h5>

    <!-- List group -->
    <items-table :data="{{ json_encode($items) }}"></items-table>

  </div>
@stop
