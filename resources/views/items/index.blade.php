@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <h5 class="card-header">
      Eksemplarer ({{ count($items) }})
    </h5>

    <div class="card-body">
      <table class="table" style="width:100%">
        <thead>
          <tr>
            <th>Ting</th>
            <th>Strekkode</th>
            <th>Merknader</th>
            <th>Utlån</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $item)
            <tr>
              <td>
                {{ $item->thing->name }}
              </td>
              <td>
                <a href="{{ URL::action('ItemsController@show', $item->id) }}">{{ $item->dokid ?: '(–)' }}</a>
              </td>
              <td>
                {{ $item->note }}
              </td>
              <td>
                @if ($loan = $item->loans->first())
                  <span class="badge badge-success">Utlånt</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {
    $('table').DataTable({
        order: [[ 1, "asc" ]],

        paging: false,
        info: false,

        // Source: https://datatables.net/plug-ins/i18n/Norwegian-Bokmal
        language: {
            "sEmptyTable": "Ingen data tilgjengelig i tabellen",
            "sInfo": "Viser _START_ til _END_ av _TOTAL_ linjer",
            "sInfoEmpty": "Viser 0 til 0 av 0 linjer",
            "sInfoFiltered": "(filtrert fra _MAX_ totalt antall linjer)",
            "sInfoPostFix": "",
            "sInfoThousands": " ",
            "sLoadingRecords": "Laster...",
            "sLengthMenu": "Vis _MENU_ eksemplarer",
            "sLoadingRecords": "Laster...",
            "sProcessing": "Laster...",
            "sSearch": "S&oslash;k:",
            "sUrl": "",
            "sZeroRecords": "Ingen linjer matcher s&oslash;ket",
            "oPaginate": {
                "sFirst": "F&oslash;rste",
                "sPrevious": "Forrige",
                "sNext": "Neste",
                "sLast": "Siste"
            },
            "oAria": {
                "sSortAscending": ": aktiver for å sortere kolonnen stigende",
                "sSortDescending": ": aktiver for å sortere kolonnen synkende"
            }
        },

    });

  });
</script>

@stop
