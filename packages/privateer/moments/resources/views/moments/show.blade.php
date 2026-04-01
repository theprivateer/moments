@extends('moments::layouts.app')

@section('content')
    @include('moments::partials.moment-card', ['moment' => $moment])
@endsection
