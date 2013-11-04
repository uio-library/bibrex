@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Lån #{{ $loan->id }}</h3>
    </div>

    <div class="panel-body">

      @if (empty($loan->deleted_at))
      <a href="{{ URL::action('LoansController@getDestroy', $loan->id) }}" style="float:right">Returnér dokument</a>
      @endif

      <table>
        <tr>
          <th>
            Ting:
          </th>
          <td>
            {{ $loan->document->thing->name }}
          </td>
        </tr>

        @if ($loan->document->thing->id == 1)
        <tr>
          <th>
            Dokid:
          </th>
          <td>
            <a href="{{ URL::action('DocumentsController@getShow', $loan->document->id) }}" style="padding-left:10px;">
              {{ $loan->document->dokid }}
            </a>
          </td>
        </tr>
        @endif

        <tr>
          <th>
            Låntaker:
          </th>
          <td>
            <a href="{{ URL::action('UsersController@getShow', $loan->user->id) }}">
              {{ $loan->user->lastname }},
              {{ $loan->user->firstname }}
              {{ $loan->as_guest? '(utlånt på midlertidig kort)' : '' }}
            </a>
          </td>
        </tr>

        <tr>
          <th>
            Utlånssted:
          </th>
          <td>
            {{ $loan->library->name }}
          </td>
        </tr>

        <tr>
          <th>
            Utlånt:
          </th>
          <td>
            {{ $loan->created_at }}
          </td>
        </tr>

        <tr>
          <th>
            Forfaller:
          </th>
          <td>
            {{ $loan->due_at }}
            {{ ($d = $loan->daysLeftFormatted()) ? "($d)" : "ukjent / aldri" }}
          </td>
        </tr>

        <tr>
          <th>
            Påminnelser:
          </th>
          <td>
            @if (count($loan->reminders) == 0)
              <div>
                <em>Ingen påminnelser sendt</em>
              </div>
            @else
              @foreach ($loan->reminders as $reminder)
                <div>
                  Påminnelse per {{ $reminder->medium == 'sms' ? 'SMS' : 'e-post' }} sendt {{ $reminder->date_created }}
                </div>
              @endforeach
            @endif
            <div>
              <a href="{{ URL::action('RemindersController@getCreate') . '?loan_id=' . $loan->id }}">
                Send påminnelse
              </a>
            </div>
          </td>
        </tr>

        <tr>
          <th>
            Returnert:
          </th>
          <td>
            {{ $loan->deleted_at }}
          </td>
        </tr>
      </table>
    </div>
  </div>

@stop
