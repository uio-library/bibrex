@extends('layouts.master')

@section('content')

    {{ Form::model($item, array(
        'action' => array( 'ItemsController@upsert', $item->id ?: '_new' ),
        'method' => 'post'
    )) }}

    <div class="card card-primary">

        <h5 class="card-header">
            @if (!$item->id)
                Nytt eksemplar
            @else
                Rediger eksemplar
            @endif
        </h5>

        <ul class="list-group list-group-flush">

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('thing', 'Ting: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col-sm-9">
                        <select name="thing" class="form-control">
                            @foreach ($things as $thing)
                                <option value="{{ $thing->id }}"{{ $item->thing && $item->thing->id == $thing->id ? ' selected="selected"' : ''}}> {{ $thing->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </li>

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('barcode', 'Strekkode: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col">
                        @component('components.text', ['name' => 'barcode', 'value' => $item->barcode])
                        @endcomponent
                    </div>
                    <a href="https://www.uio.no/for-ansatte/enhetssider/ub/publikumsarbeid/bibrex.html" target="_blank" class="btn btn-link col col-sm-1 mx-1">
                        <i class="far fa-question-circle"></i>
                        Hjelp
                    </a>

                </div>
            </li>

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('note', 'Eksemplarinfo: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col-sm-9">
                        @component('components.text', ['name' => 'note', 'value' => $item->note])
                        @endcomponent
                    </div>
                </div>
            </li>
        </ul>

        <div class="card-footer">
            <a href="{{ URL::action('ItemsController@index') }}" class="btn btn-default">Avbryt</a>
            {{ Form::submit('Lagre', array('class' => 'btn btn-success')) }}
        </div>

    </div>

    {{ Form::close() }}

@stop


@section('scripts')

    <script>
        document.querySelector('input[type="text"]:not([readonly])').focus();
    </script>

@stop
