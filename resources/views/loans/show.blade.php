@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <h5 class="card-header">
      Lån #{{ $loan->id }}
    </h5>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Ting:
                </div>
                <div class="col">
                    <a href="{{ URL::action('ThingsController@getShow', $loan->item->thing->id) }}">{{ $loan->item->thing->name }}</a>
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Eksemplar:
                </div>
                <div class="col">
                    @if ($loan->item->dokid)
                        <a href="{{ URL::action('ItemsController@show', $loan->item->id) }}">{{ $loan->item->dokid }}</a>
                    @else
                        <em>ikke registrert</em>
                    @endif
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Låntaker:
                </div>
                <div class="col">
                    <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
                      {{ $loan->user->lastname }},
                      {{ $loan->user->firstname }}
                      {{ $loan->as_guest? '(utlånt på midlertidig kort)' : '' }}
                    </a>
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Utlånssted:
                </div>
                <div class="col">
                    {{ $loan->library->name }}
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Utlånt:
                </div>
                <div class="col">
                    {{ $loan->created_at }}
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Forfaller:
                </div>
                <div class="col">
                    {{ $loan->due_at }}
                    @if (!$loan->trashed())
                        {!! ($d = $loan->daysLeftFormatted()) ? "($d)" : "ukjent / aldri" !!}
                    @endif
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Returnert:
                </div>
                <div class="col">
                    @if ($loan->is_lost)
                        <span class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Markert som tapt
                            {{ $loan->deleted_at }}
                        </span>
                    @else
                        {{ $loan->deleted_at ?: 'ikke returnert enda' }}
                    @endif
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Påminnelser:
                </div>
                <div class="col">
                    @if (count($loan->reminders) == 0)
                      <div>
                        <em>Ingen påminnelser sendt</em>
                      </div>
                    @else
                      @foreach ($loan->reminders as $reminder)
                        <div>
                          <a href="{{ URL::action('RemindersController@getShow', $reminder->id) }}">Påminnelse</a> per {{ $reminder->medium == 'sms' ? 'SMS' : 'e-post' }} sendt {{ $reminder->created_at }}
                        </div>
                      @endforeach
                    @endif
                    @if (!$loan->trashed())
                        <div>
                          <a href="{{ URL::action('RemindersController@getCreate') . '?loan_id=' . $loan->id }}">
                            Send manuell påminnelse
                          </a>
                        </div>
                    @endif
                </div>
            </div>
        </li>
    </ul>
</div>


@stop
