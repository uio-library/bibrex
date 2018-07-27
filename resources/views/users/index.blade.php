@extends('layouts.master')

@section('content')

  <div class="card">

     <div class="card-header" >
        <div class="row align-items-center">
            <h5 class="col mb-0">
                Brukere (<span id="usercount">{{ count($users) }}</span>)
            </h5>

            <a href="{{ URL::action('UsersController@getEdit', '_new') }}" class="col col-auto mx-1 btn btn-success">
                <i class="far fa-user-plus"></i>
                Ny bruker
            </a>
        </div>
    </div>

    <!-- List group -->
    <users-table :data="{{ json_encode($users) }}"></users-table>

  </div>

@stop
