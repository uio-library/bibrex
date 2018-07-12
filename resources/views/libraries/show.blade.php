@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">Bibliotek #{{$library->id}}</h5>
        </div>
    </div>

    <div class="card-body">

      Navn: {{$library->name}} / {{$library->name_eng}}<br />
      Epost: {{$library->email}}<br />

    </div>
  </div>

@stop

@section('scripts')

@stop
