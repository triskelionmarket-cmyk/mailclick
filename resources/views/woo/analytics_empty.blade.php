@extends('layouts.core.frontend')

@section('title', trans('messages.analytics_recommendations'))

@section('page_header')
    <div class="page-title">
        <h1><span class="material-symbols-rounded me-2">analytics</span> {{ trans('messages.analytics_recommendations') }}</h1>
        <p class="text-muted">{{ trans('messages.woo.empty_connect_desc') }}</p>
    </div>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 text-center p-5 my-4">
    <div class="mb-3">
        <span class="material-symbols-rounded" style="font-size:72px;color:#007bff;">shopping_cart</span>
    </div>
    <h3 class="fw-bold mb-2">{{ trans('messages.woo.empty_no_store') }}</h3>
    <p class="text-muted mb-4 max-w-md mx-auto">{{ trans('messages.woo.empty_connect_instructions') }}</p>
    <div>
        <a href="{{ action('SourceController@create') }}" class="btn btn-primary btn-lg rounded-3 fw-bold px-4 me-2">
            <span class="material-symbols-rounded me-1">add_circle</span> {{ trans('messages.woo.empty_add_store') }}
        </a>
    </div>
</div>
@endsection
