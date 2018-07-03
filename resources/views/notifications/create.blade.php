@extends('layouts.master')

@section('content')


    <form action="{{ action('NotificationsController@send', $loan->id) }}" class="card card-primary" method="post">
        {{ csrf_field() }}

        <h5 class="card-header">
          Send ny påminnelse per epost
        </h5>

        <ul class="list-group list-group-flush">

            <li class="list-group-item">
                <div class="row">
                    <div class="col-sm-2">
                        Fra:
                    </div>
                    <div class="col">
                        @if ($email['sender_mail'])
                            <input type="hidden" name="medium" value="email">
                            {{ $email['sender_mail']}}
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
                        @if ($email['receiver_mail'])
                            <input type="hidden" name="medium" value="email">
                            {{ $email['receiver_mail']}}
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
                        <input type="text" name="subject" value="{{ $email['subject'] }}" class="form-control">
                    </div>
                </div>
            </li>

            <li class="list-group-item">
                <div class="row">
                    <div class="col-sm-2">
                        Melding:
                    </div>
                    <div class="col">
                        <textarea class="form-control" name="body" cols="60" rows="10">{{ $email['body'] }}</textarea>
                    </div>
                </div>
            </li>
        </ul>

        <!--<textarea name="comment" style="width: 600px; height: 60px;">Oi, oi, oi, "{{ $loan->representation(true) }}" har forfalt.</textarea>-->
        <input type="hidden" name="loan_id" value="{{ $loan->id }}">

        <div class="card-footer">
            <a href="{{ URL::action('LoansController@getShow', $loan->id) }}" class="btn">Avbryt</a>
            <button type="submit" class="btn btn-success">Send påminnelse</button>
        </div>

    </form>

@stop
