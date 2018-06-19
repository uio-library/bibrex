@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <h5 class="card-header">
      Eksemplarer ({{ count($items) }})
    </h5>

    <!-- List group -->
    <ul class="list-group list-group-flush">
      @foreach ($items as $item)
        <li class="list-group-item">
          <a href="{{ URL::action('ItemsController@getShow', $item->id) }}">
              @if ($item->dokid)
                {{$item->dokid}} {{$item->title}} ({{$item->thing->name}})
              @else
                {{$item->thing->name}}
              @endif
            </a>
            @if ($item->dokid)
                  @if ($loan = $item->loans->first())
                  <span class="badge badge-success">Utlånt</span>
                  @endif
            @else
                  <span class="badge badge-success">{{ $item->loans->count() }} utlånt</span>
            @endif

        </li>
      @endforeach
    </ul>

  </div>

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop
