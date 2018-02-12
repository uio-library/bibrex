@extends('layouts.master')

@section('content')

  {{ Form::model($reminder, array(
      'action' => array('RemindersController@postStore'),
      'class' => 'panel panel-primary',
      'method' => 'post'
  )) }}

    <div class="panel-heading">
      <h3 class="panel-title">Send ny påminnelse</h3>
    </div>

    <div class="panel-body form-horizontal">

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
        <label for="recipient" class="col-sm-2 control-label">Emne:</label>
        <div class="col-sm-10">
          <p class="form-control-static">
            {{ $subject }}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="recipient" class="col-sm-2 control-label">Melding:</label>
        <div class="col-sm-10">
          <p class="form-control-static">
            {{ $body }}
          </p>
        </div>
      </div>

    </div>

    <!--<textarea name="comment" style="width: 600px; height: 60px;">Oi, oi, oi, "{{ $loan->representation(true) }}" har forfalt.</textarea>-->
    <input type="hidden" name="loan_id" value="{{ $loan->id }}">

    <div class="panel-footer">
      <a href="{{ URL::action('LoansController@getShow', $loan->id) }}" class="btn">Avbryt</a>
      {{ Form::submit('Send påminnelse', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop