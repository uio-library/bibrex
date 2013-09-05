@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Dokument #{{ $document->id }}</h3>
    </div>

    @if ($document->cover_image)
      <img src="{{ $document->cover_image }}" style="float:left;" />
    @endif

    <div class="row">
      <div class="col-2">
        <strong>Ting:</strong>
      </div>
      <div class="col-6">
        {{ $document->thing->name }}
      </div>
    </div>

    @if ($document->thing->id == "1")

      <hr>

      <div class="row">
        <div class="col-2">
          <strong>Dokid:</strong>
        </div>
        <div class="col-6">
          {{ $document->dokid }}
        </div>
      </div>

      <div class="row">
        <div class="col-2">
          <strong>Objektid:</strong>
        </div>
        <div class="col-6">
          <a href="http://ask.bibsys.no/ask/action/show?pid={{ $document->objektid }}&amp;kid=biblio">
            {{ $document->objektid }}
          </a>
        </div>
      </div>

      <div class="row">
        <div class="col-2">
          <strong>Tittel:</strong>
        </div>
        <div class="col-6" id="title">
          {{ $document->title }}        
        </div>
      </div>

      <div class="row">
        <div class="col-2">
          <strong>Forfatter:</strong>
        </div>
        <div class="col-6" id="author">

        </div>
      </div>

    @endif

    <h3>Lånehistorikk</h3>
    @foreach ($loans = $document->allLoans as $nr => $loan)
      <hr>

      <big class="col-1" style="float:right;">{{ (count($loans) - $nr) }}</big>
      <div class="row">
        <div class="col-2">
          <strong>Låntaker:</strong>
        </div>
        <div class="col-6">
          <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
            {{ $loan->user->lastname }},
            {{ $loan->user->firstname }}
          </a>
        </div>
      </div>

      <div class="row">
        <div class="col-2">
          <strong>Utlånt:</strong>
        </div>
        <div class="col-6">
          {{ $loan->created_at }}
        </div>
      </div>

      <div class="row">
        <div class="col-2">
          <strong>Returnert:</strong>
        </div>
        <div class="col-6">
          {{ $loan->deleted_at }}
        </div>
      </div> 

    @endforeach

    @if (count($loans) == 0)
      <em>Ingen utlån</em>
    @endif

  </div>

@stop


@section('scripts')

<script type='text/javascript'>     
  $(document).ready(function() {

  });
</script>

@stop