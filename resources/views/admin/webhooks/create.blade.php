@extends('layouts.core.backend', [
	'menu' => 'webhook',
])

@section('title', trans('messages.webhook.add_new'))

@section('head')
	<script type="text/javascript" src="{{ AppUrl::asset('core/tinymce/tinymce.min.js') }}"></script>        
    <script type="text/javascript" src="{{ AppUrl::asset('core/js/editor.js') }}"></script>
@endsection

@section('page_header')

	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li class="breadcrumb-item"><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ action("Admin\WebhookController@index") }}">{{ trans('messages.webhook.webhooks') }}</a></li>
		</ul>
		<h1 class="mc-h1">
			<span class="text-semibold">{{ trans('messages.webhook.add_new') }}</span>
		</h1>
	</div>

@endsection

@section('content')

<div class="row">
	<div class="col-sm-12 col-md-10 col-lg-10">
		<p>{!! trans('messages.webhook.wording') !!}</p>
	</div>
</div>

@php
	$formId = 'WebhookForm' . uniqid();
@endphp

<form id="{{ $formId }}" action="{{ action('Admin\WebhookController@store') }}" method="POST" class="">
	@csrf

	@include('admin.webhooks._form')

	{{-- @include('admin.webhooks.setup', [
		'formId' => $formId,
	]) --}}

	<hr >
	<div class="text-left">
		<button class="btn btn-secondary me-1"><i class="icon-check"></i> {{ trans('messages.save') }}</button>
		<a href="{{ action('Admin\WebhookController@index') }}" class="btn btn-light me-2"><i class="icon-check"></i> {{ trans('messages.cancel') }}</a>
	</div>
</form>
    
@endsection
