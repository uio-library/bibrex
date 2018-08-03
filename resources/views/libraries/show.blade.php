@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <div class="card-header">
        <div class="row align-items-center">
            <h5 class="col mb-0">Bibliotek #{{$library->id}}</h5>
        </div>
    </div>

    <div class="card-body">

      Navn: {{$library->name}} / {{$library->name_eng}}<br>
      Epost: {{$library->email}}<br>
      Bibliotekskode: {{ $library->library_code ?: '(har ikke)' }}<br>
      Midlertidig lånekort: {{ $library->temporary_barcode ?: '(ikke aktivert)' }}<br>
    </div>
  </div>

@stop

@section('scripts')

@stop
