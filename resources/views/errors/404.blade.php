@extends('layouts.master')

@section('content')
	<div class="card bg-danger text-white">
		<div class="card-body">
    		{{ $what ?? 'Siden' }} finnes ikke.
		</div>
	</div>
@stop
