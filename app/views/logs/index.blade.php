@extends('layouts.master')

@section('content')

	<h2>Logg ({{ count($items) }})</h2>

	<table style="width:100%;">
    @foreach ($items as $item)
    	<tr data-content="{{{ $item[0] }}}">
    		<td valign="top" style="white-space:nowrap">
    			<small style="white-space:nowrap">{{ $item[1]['date'] }}</small>
    		</td>
    		<td valign="top" style="white-space:nowrap">
    			<span class="label label-{{ strtolower($item[1]['level']) != 'error' ? strtolower($item[1]['level']) : 'danger' }}">{{ $item[1]['level'] }}</span>
    		</td>
    		<td>
    			<?php
                    if (strpos($item[1]['message'], PHP_EOL) !== false) {
                        $spl = explode(PHP_EOL, $item[1]['message']);
                        $i0 = array_shift($spl);
                        echo '<div><a href="#" onclick="$(this).parent().next(\'.message-collapsed\').toggle(); return false;"">' . $i0 . '</a></div>';
                        echo '<div class="message-collapsed" style="display:none;">' . implode('<br />', $spl) . '</div>';
                    } else {
                        echo $item[1]['message'];
                    }
                ?>
    		</td>
            <td>
                <a href="#" class="del-link">Slett</a>
            </td>
    	</tr>
    @endforeach
	</table>
@stop


@section('scripts')

<script type="text/javascript">

    $('.del-link').on('click', function(e) {
        e.preventDefault();
        var url = "{{ URL::action('LogsController@postDestroy') }}";
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