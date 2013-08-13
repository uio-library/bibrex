@extends('layouts.master')

@section('content')
    <p class="alert alert-danger">
    	{{ isset($what) ? $what : 'Siden' }}
    	finnes ikke.
   	</p>
@stop
