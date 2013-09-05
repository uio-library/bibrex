@extends('layouts.master')

@section('content')

  {{ Form::model($reminder, array(
      'action' => array('RemindersController@postStore'),
      'class' => 'panel panel-primary',
      'method' => 'post'
  )) }}

    <div class="panel-heading">
      <h3 class="panel-title">Send påminnelse</h3>
    </div>

    <div class="form-group">
      Til: {{ Form::radio('medium', 'sms', false, array('id' => 'medium-sms')) }}
      {{ Form::label('medium-sms', 'SMS: ' . $loan->user->phone) }}
      {{ Form::radio('medium', 'epost', true, array('id' => 'medium-epost')) }}
      {{ Form::label('medium-epost', 'E-post: ' . $loan->user->email) }}
    </div>

    <textarea style="width: 600px; height: 60px;">Oi, oi, oi, "{{ $loan->representation(true) }}" har forfalt.</textarea>

    <div class="panel-footer">
      <a href="{{ URL::action('LoansController@getShow', $loan->id) }}" class="btn">Avbryt</a>
      {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
    </div>

  {{ Form::close() }}

@stop


@section('scripts')

<script type='text/javascript'>
  $(document).ready(function() {

  });
</script>

@stop