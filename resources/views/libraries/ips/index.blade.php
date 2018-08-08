@extends('layouts.master')

@section('content')

  <div class="card card-primary">

    <div class="card-header">
      <h3 class="card-title">IP-adresser for autopålogging</h3>
    </div>

    <div class="card-body">
    	<p>
    		Her konfigurerer du IP-adresser for autopålogging.
    		Sitter du på en maskin med en adresse konfigurert på
    		denne siden vil du bli automatisk logget inn.
    	</p>
    </div>

    <div class="card-body">

      {{ Form::model(new App\LibraryIp(), array(
          'action' => 'LibrariesController@storeIp',
          'class' => 'form-inline'
          )) }}

        Ny IP:
        {{ Form::text('ip', null, array(
            'placeholder' => 'IP-adresse',
            'class' => 'form-control',
            'style' => 'width:200px'
        )) }}

        <button type="submit" class="btn btn-success">
          Legg til
        </button>

        <img src="/img/spinner2.gif" class="spinner" />

      {{ Form::close() }}

    </div>

    <!-- List group -->

    <table style="width:100%" class="table">
      <thead>
        <tr>
          <th>IP-adresse</th>
          <th>Lagt til</th>
          <th>Sist i bruk</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($library->ips as $ip)
        <tr>
          <td>
            {{ $ip->ip }}
          </td>
          <td>
            {{ $ip->created_at }}
          </td>
          <td>
            {{ $ip->last_used }}
          </td>
          <td>
            <form action="{{ URL::action('LibrariesController@removeIp', $ip->id) }}" method="post">
              {{ csrf_field() }}
              <button type="submit" class="btn btn-xs btn-danger">
                Fjern
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

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
