@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <div class="card-header" >
        <div class="row align-items-center">

            @if ($item->trashed())

              <h5 class="col mb-0">
                <s>Eksempler #{{ $item->id }}</s> (slettet)
              </h5>

              <a class="btn btn-warning col col-auto mx-1" href="{{ URL::action('ItemsController@getRestore', $item->id) }}">
                <i class="far fa-box-full"></i>
                Gjenopprett
              </a>

            @else

              <h5 class="col mb-0">Eksempler #{{ $item->id }}</h5>

              <a href="{{ URL::action('ItemsController@getEdit', $item->id) }}" class="col col-auto mx-2 btn btn-primary">
                  <i class="far fa-pencil-alt"></i>
                  Rediger
              </a>

              <a class="btn btn-warning col-auto mx-1" href="{{ URL::action('ItemsController@getDelete', $item->id) }}">
                  <i class="far fa-trash"></i>
                  Slett
              </a>

            @endif

        </div>
    </div>

      <ul class="list-group list-group-flush">

          <li class="list-group-item">
              <div class="row">
                  <div class="col-sm-2">
                      Ting:
                  </div>
                  <div class="col">
                      <a href="{{ URL::action('ThingsController@getShow', $item->thing->id) }}">{{ $item->thing->name }}</a>
                  </div>
              </div>
          </li>

          <li class="list-group-item">
              <div class="row">
                  <div class="col-sm-2">
                      Strekkode:
                  </div>
                  <div class="col">
                      {{ $item->dokid ?: '(ingen)' }}
                  </div>
              </div>
          </li>

          <li class="list-group-item">
              <div class="row">
                  <div class="col-sm-2">
                      Merknad:
                  </div>
                  <div class="col">
                      {{ $item->note }}
                  </div>
              </div>
          </li>

      </ul>


{{--
      @if ($item->cover_image)
        <img src="{{ $item->cover_image }}" style="float:right;" />
      @endif
        <strong>Objektid:</strong>
        <a href="http://ask.bibsys.no/ask/action/show?pid={{ $item->objektid }}&amp;kid=biblio">
          {{ $item->objektid }}
        </a><br />

        <strong>Tittel:</strong>
        {{ $item->title }} {{ $item->subtitle }}<br />

        <strong>Forfatter:</strong>
        {{ $item->authors }}<br />

      @endif--}}
    </div>

      {{--

    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <h5>Lånehistorikk</h5>
        </li>
    </ul>

      <table class="table">
      @foreach ($loans = $item->allLoans as $nr => $loan)
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
      --}}


  </div>

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop
