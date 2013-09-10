@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Dokument #{{ $document->id }}</h3>
    </div>

    <div class="panel-body">

      @if ($document->cover_image)
        <img src="{{ $document->cover_image }}" style="float:right;" />
      @endif

      <strong>Ting:</strong>
      {{ $document->thing->name }}<br />

      @if ($document->thing->id == "1")

        <strong>Dokid:</strong>
        {{ $document->dokid }}<br />

        <strong>Objektid:</strong>
        <a href="http://ask.bibsys.no/ask/action/show?pid={{ $document->objektid }}&amp;kid=biblio">
          {{ $document->objektid }}
        </a><br />

        <strong>Tittel:</strong>
        {{ $document->title }} {{ $document->subtitle }}<br />

        <strong>Forfatter:</strong>
        {{ $document->authors }}<br />

      @endif

      <h3>Lånehistorikk</h3>
      <table class="table">
      @foreach ($loans = $document->allLoans as $nr => $loan)
        <tr>
          <td>
            <span class="badge">{{ (count($loans) - $nr) }}</span>
          </td>
          <td>
            <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
              {{ $loan->user->lastname }},
              {{ $loan->user->firstname }}
            </a>
          </td>
          <td>
            {{ $loan->created_at }}
          </td>
          <td>
            {{ $loan->deleted_at }}
          </td>
        </tr>
      @endforeach
      </table>

      @if (count($loans) == 0)
        <em>Ingen utlån</em>
      @endif

    </div>

  </div>

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop