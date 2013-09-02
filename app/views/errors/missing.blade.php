@extends('layouts.master')

@section('content')
    <p class="alert alert-danger" style="display:none;">
    	{{ isset($what) ? $what : 'Siden' }}
    	finnes ikke.
   	</p>
@stop
