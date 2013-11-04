@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Bibliotek #{{$library->id}}</h3>
    </div>

    <div class="panel-body">

      Navn: {{$library->name}}<br />
      Epost: {{$library->email}}<br />
      Gjeste-LTID: {{$library->guest_ltid}}

    </div>
  </div>

@stop

@section('scripts')

@stop