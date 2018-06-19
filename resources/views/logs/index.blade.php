@extends('layouts.master')

@section('content')

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <h5 class="col mb-0">Logg</h5>
            </div>
        </div>

        <div class="card-body">
            <table>
                @foreach ($items as $item)
                    <tr>
                        <td valign="top" style="white-space:nowrap">
                            <small style="white-space:nowrap">{{ $item->time }}</small>
                        </td>
                        <td valign="top" style="white-space:nowrap">
                            <span class="badge badge-{{ strtolower($item->level_name) != 'error' ? strtolower($item->level_name) : 'danger' }}">{{ $item->level_name }}</span>
                        </td>
                        <td>
                            <?php
                            if (strpos($item->message, PHP_EOL) !== false) {
                                $spl = explode(PHP_EOL, $item->message);
                                $i0 = array_shift($spl);
                                echo '<div><a href="#" onclick="$(this).parent().next(\'.message-collapsed\').toggle(); return false;"">' . $i0 . '</a></div>';
                                echo '<div class="message-collapsed" style="display:none;">' . implode('<br />', $spl) . '</div>';
                            } else {
                                echo $item->message;
                            }
                            ?>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
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
