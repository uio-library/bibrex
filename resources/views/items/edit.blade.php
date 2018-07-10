@extends('layouts.master')

@section('content')

    {{ Form::model($item, array(
        'action' => array( 'ItemsController@upsert', $item->id ?: '_new' ),
        'method' => 'post'
    )) }}

    <div class="card card-primary">

        <h5 class="card-header">
            @if (!$item->id)
                Registrer nytt eksemplar
            @else
                Rediger eksemplar
            @endif
        </h5>

        <ul class="list-group list-group-flush">

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('thing', 'Ting: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" id="staticType" value="{{  $item->thing->name }}">
                        <input type="hidden" name="thing" value="{{ $item->thing->id }}">
                    </div>
                </div>
            </li>

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('barcode', 'Strekkode: ', ['class' => 'col-sm-3 col-form-label']) }}
                    <div class="col-sm-9">
                        @component('components.text', ['name' => 'barcode', 'value' => $item->barcode])
                        @endcomponent
                    </div>
                </div>
            </li>

            <li class="list-group-item">
                <div class="row">
                    {{ Form::label('note', 'Merknad: ', ['class' => 'col-sm-3 col-form-label']) }}
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
