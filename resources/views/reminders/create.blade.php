@extends('layouts.master')

@section('content')

  {{ Form::model($reminder, array(
      'action' => array('RemindersController@postStore'),
      'class' => 'card card-primary',
      'method' => 'post'
  )) }}

    <h5 class="card-header">
      Send ny påminnelse
    </h5>

    <ul class="list-group list-group-flush">

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Fra:
                </div>
                <div class="col">
                    @if ($reminder->sender_mail)
                        <input type="hidden" name="medium" value="email">
                        {{ $reminder->sender_mail}}
                    @else
                        <span class="text-danger">Oh my, no email registered</span>
                    @endif
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Til:
                </div>
                <div class="col">
                    @if ($reminder->receiver_mail)
                        <input type="hidden" name="medium" value="email">
                        {{ $reminder->receiver_mail}}
                    @else
                        <span class="text-danger">Oh my, no email registered</span>
                    @endif
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Emne:
                </div>
                <div class="col">
                    {{ $subject }}
                </div>
            </div>
        </li>

        <li class="list-group-item">
            <div class="row">
                <div class="col-sm-2">
                    Melding:
                </div>
                <div class="col">
                    {!! $body !!}
                </div>
            </div>
        </li>
    </ul>

    <!--<textarea name="comment" style="width: 600px; height: 60px;">Oi, oi, oi, "{{ $loan->representation(true) }}" har forfalt.</textarea>-->
    <input type="hidden" name="loan_id" value="{{ $loan->id }}">

    <div class="card-footer">
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
