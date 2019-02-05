@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">
              Eksemplarer ({{ count($items) }})
            </h5>
            <a href="{{ URL::action('ItemsController@editForm', '_new') }}" class="col col-auto mr-2 btn btn-success">
                <i class="far fa-plus-hexagon"></i>
                Nytt eksemplar
            </a>
        </div>
    </div>

    <!-- List group -->
    <items-table :data="{{ json_encode($items) }}" :show-thing="true"></items-table>

  </div>
@stop
