@extends('layouts.master')

@section('content')

    <div class="panel-heading">
      <h3 class="panel-title">Vis påminnelse</h3>
    </div>

    <div class="panel-body form-horizontal">

      <div class="form-group">
        <label for="recipient" class="col-sm-2 control-label">Fra:</label>
        <div class="col-sm-10">
          <p class="form-control-static">
            <input type="hidden" name="medium" value="email">
            {{ $from }}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="recipient" class="col-sm-2 control-label">Til:</label>
        <div class="col-sm-10">
          <p class="form-control-static">
            <input type="hidden" name="medium" value="email">
            {{ $loan->user->email }}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="recipient" class="col-sm-2 control-label">Dato:</label>
        <div class="col-sm-10">
          <p class="form-control-static">
            {{ $reminder->created_at }}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="recipient" class="col-sm-2 control-label">Emne:</label>
        <div class="col-sm-10">
          <p class="form-control-static">
            {{ $reminder->subject }}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="recipient" class="col-sm-2 control-label">Melding:</label>
        <div class="col-sm-10">
          <p class="form-control-static">
            {{ preg_replace('/\n/', '<br>', $reminder->body) }}
          </p>
        </div>
      </div>

    </div>

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop