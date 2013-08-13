
@section('content')

  <div class="panel panel-primary">

    <div class="panel-heading">
      <h3 class="panel-title">Dokumenter</h3>
    </div>

    <p>
        Et dokument er en instans av en dings/ting.
    </p>

    <!-- List group -->
    <ul class="list-group">
      @foreach ($documents as $doc)
        <li class="list-group-item">
    	  @if ($loan = $doc->loans->first())
          <span class="badge">Utlånt</span>
          @endif
          <a href="{{ URL::action('DocumentsController@getShow', $doc->id) }}">
              @if ($doc->thing->id == '1')
                {{$doc->dokid}} {{$doc->title}}
              @else
                {{$doc->thing->name}}
              @endif
            </a>
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
