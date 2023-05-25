@extends('cabinet.emails.layouts.layout')

@section('content')
    @if ($data->title_message)
        @if (\Illuminate\Support\Facades\Lang::has('cratos.'.$data->title_message))
            <h1>{!! t($data->title_message, json_decode($data->title_params, true)) !!}</h1>
        @else
            <h1>{!! $data->title_message !!}</h1>
        @endif
    @endif
    @if (\Illuminate\Support\Facades\Lang::has('cratos.'.$data->body_message))
        <p class="overflow-wrap: break-word;">{!! t($data->body_message, json_decode($data->body_params, true)) !!}</p>
    @else
        <p class="overflow-wrap: break-word;">{!! $data->body_message !!}</p>
    @endif
@endsection
