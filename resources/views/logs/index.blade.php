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
            <table class="table table-striped table-sm">
                @foreach ($items as $item)
                    <tr>
                        <td valign="top" style="white-space:nowrap; padding-left: 20px">
                            <small style="white-space:nowrap">{{ $item->time }}</small>
                        </td>
                        <td valign="top" style="white-space:nowrap">
                            <span class="badge badge-{{ strtolower($item->level_name) != 'error' ? strtolower($item->level_name) : 'danger' }}">{{ $item->level_name }}</span>
                        </td>
                        <td>
                            <?php
                            if (strpos($item->message, PHP_EOL) !== false) {
                                $spl = explode(PHP_EOL, htmlspecialchars($item->message));
                                $i0 = array_shift($spl);
                                echo '<div><a href="#" onclick="$(this).parent().next(\'.message-collapsed\').toggle(); return false;">' . $i0 . '</a></div>';
                                echo '<div class="message-collapsed" style="display:none;">' . implode('<br>', $spl) . '</div>';
                            } else {
                                echo $item->message;
                            }
                            ?>
                        </td>
                        <td style="white-space:nowrap; text-align: right; padding-right: 20px;">
                                                        <small>{{ array_get($item->context, 'library') }}</small>

                        </td>
                    </tr>
                @endforeach
            </table>
    </div>

@stop


@section('scripts')

<script type="text/javascript">

    $('.del-link').on('click', function(e) {
        e.preventDefault();
        var url = "{{ action('LogsController@postDestroy') }}";
        var data = $(e.target).closest('tr').data('content');
        $.post(url, { content: data })
        .done(function(response) {
            if (response.success) {
                $(e.target).closest('tr').remove();
            } else {
                alert('Oi, det oppsto en feil');
            }
        });
    });

</script>

@stop
