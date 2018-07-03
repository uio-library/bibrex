@extends('layouts.master')

@section('content')

  <div class="card mb-3">
    <div class="card-header">
      <div class="row align-items-center">
        <h5 class="col mb-0">
          {{ $type }} sendt {{ $sent }} for
          <a href="{{ action('LoansController@getShow', $loan->id) }}">lån #{{ $loan->id }}</a>
        </h5>
      </div>
    </div>

    @if ($email)

    <ul class="list-group list-group-flush">

      <li class="list-group-item">
        <div class="row">
          <div class="col-sm-3">
            Fra:
          </div>
          <div class="col">
            {{ $email['sender_name'] }} ({{ $email['sender_mail'] }})
          </div>
        </div>
      </li>

      <li class="list-group-item">
        <div class="row">
          <div class="col-sm-3">
            Til:
          </div>
          <div class="col">
            {{ $email['receiver_name'] }} ({{ $email['receiver_mail'] }})
          </div>
        </div>
      </li>

      <li class="list-group-item">
        <div class="row">
          <div class="col-sm-3">
            Emne:
          </div>
          <div class="col">
            {{ $email['subject'] }}
          </div>
        </div>
      </li>

      <li class="list-group-item">
        <div class="row">
          <div class="col-sm-3">
            Melding:
          </div>
          <div class="col">
            <p class="form-control-static">
              {!! preg_replace('/\n/', '<br>', $email['body']) !!}
            </p>
          </div>
        </div>
      </li>

    </ul>

    @endif

@stop
