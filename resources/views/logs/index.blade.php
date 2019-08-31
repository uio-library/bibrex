@extends('layouts.master')

@section('content')

    <div class="card" v-pre><!--

        NOTE: v-pre skips Vue compilation for this element and all its children!

        -->
        <div class="card-header">
            <div class="row align-items-center">
                <h5 class="col mb-0">Logg</h5>
            </div>
        </div>

        <div class="card-body">
            <p>
                Loggmeldinger oppbevares i {{ config('logging.channels.postgres.days') }} dager før de slettes.
            </p>
        </div>
            <table class="table table-striped table-sm" style="font-size: 85%">
                @foreach ($entries as $entry)
                    <tr>
                        <td valign="top" style="white-space:nowrap; padding-left: 20px">
                            <samp style="white-space:nowrap">{{ $entry->time }}</samp>
                        </td>
                        <td valign="top" style="white-space:nowrap">
                            <span class="badge badge-{{ strtolower($entry->level_name) != 'error' ? strtolower($entry->level_name) : 'danger' }}">{{ $entry->level_name }}</span>
                        </td>
                        <td>
                            @if (count($entry->lines) == 1)
                                <samp>{!! $entry->lines[0] !!}</samp>
                            @else
                                <div>
                                    <a href="#" onclick="$(this).parent().next('.message-collapsed').toggle(); return false;">
                                        <samp>{{ $entry->lines[0] }}</samp>
                                    </a>
                                </div>
                                <div class="message-collapsed" style="display: none;">
                                    <samp>
                                        @foreach (array_slice($entry->lines, 1) as $line)
                                            {{ $line }}<br>
                                        @endforeach
                                    </samp>
                                </div>
                            @endif
                        </td>
                        <td style="white-space:nowrap; text-align: right; padding-right: 20px;">
                            @if (\Illuminate\Support\Arr::has($entry->context, 'library'))
                                <a href="{{ action('LogEntryController@index', ['library' => \Illuminate\Support\Arr::get($entry->context, 'library')]) }} " class="badge badge-warning">
                                    {{ \Illuminate\Support\Arr::get($entry->context, 'library') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
    </div>

@stop

