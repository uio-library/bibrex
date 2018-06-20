@extends('layouts.master')

@section('content')

    {{ Form::model($loan, array(
        'action' => array( 'LoansController@update', $loan->id ),
        'method' => 'post'
    )) }}

    <div class="card card-primary">

        <h5 class="card-header">
            Rediger lån
        </h5>

        <ul class="list-group list-group-flush">

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('thing', 'Ting: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" id="staticType" value="{{  $loan->item->thing->name }}">
                    </div>
                </div>
            </li>

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('thing', 'Eksemplar: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" id="staticType" value="{{  $loan->item->dokid }}">
                    </div>
                </div>
            </li>

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('due_at', 'Forfallsdato: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col-sm-9">
                        @component('components.text', [
                            'name' => 'due_at',
                            'value' => $loan->due_at->toDateString(),
                            'type' => 'date',
                        ])
                        @endcomponent
                    </div>
                </div>
            </li>

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('note', 'Merknad: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col-sm-9">
                        @component('components.text', ['name' => 'note', 'value' => $loan->note])
                        @endcomponent
                    </div>
                </div>
            </li>
        </ul>

        <div class="card-footer">
            <a href="{{ URL::action('LoansController@getShow', $loan->id) }}" class="btn btn-default">Avbryt</a>
            {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
        </div>

    </div>

    {{ Form::close() }}

@stop
