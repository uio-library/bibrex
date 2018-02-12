@extends('layouts.master')

@section('content')

  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">Ny ting</h3>
    </div>

    <div class="panel-body">

      <p>
        Finner du ikke tingen din?
      </p>

      <a class="btn btn-success" href="{{ URL::action('ThingsController@getEdit', '_new') }} ">Opprett en ny ting!</a>

    </div>

  </div>

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Ting ({{ count($things) }})</h3>
    </div>

    <div class="panel-body">

      <p>
          En ting er en klasse av dokumenter.
      </p>

    </div>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($things as $thing)
        <li class="list-group-item" style="display:flex; {{ $thing->disabled ? 'background: #eee; color: #999': ''}}">
          <div style="flex: 1 0 auto">
            <a href="{{ URL::action('ThingsController@getShow', $thing->id) }}">
              {{ $thing->name }}
            </a>
            @if ($thing->disabled)
              – Nye utlån tillates ikke
            @endif
            <div>
              @if ($thing->send_reminders)
                Ubestemt form: {{ $thing->email_name_nor }} / {{ $thing->email_name_eng }}<br>
                Bestemt form: {{ $thing->email_name_definite_nor }} / {{ $thing->email_name_definite_eng }}
              @else
                <em>Purres ikke</em>
              @endif
            </div>
          </div>
          <div>
            @if (count($thing->activeLoans()))
              <strong>{{ count($thing->activeLoans()) }} {{ $thing->num_items ? 'av ' . $thing->num_items : '' }} utlånt nå</strong>
            @else
             <span style="color:#999">
              {{ count($thing->activeLoans()) }} {{ $thing->num_items ? 'av ' . $thing->num_items : '' }} utlånt nå
            </span>
            @endif
          </div>
        </li>
      @endforeach
    </ul>

  </div>

@stop

@section('scripts')

<script type='text/javascript'>

  $(document).ready(function() {
    $('.spinner').hide();
    $('form').on('submit', function(e) {
      $('.spinner').show();
      $('input[type="button"]').prop('disabled', true);
      return true;
    });
  });

</script>

@stop
