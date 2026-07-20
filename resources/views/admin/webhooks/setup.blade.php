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
		<div class="d-flex align-items-center mb-4">
			<div class="me-4">
				<span class="material-symbols-rounded fs-3">
					settings_input_component
				</span>
			</div>
			<div>
				<h3 class="mb-1">{{ $webhook->name }}</h3>
				<p class="mb-0">{{ trans('messages.webhook.event.' . $webhook->event . '.wording') }}</p>
			</div>
		</div>
	</div>

@endsection

@section('content')
	@php
		$formId = 'WebhookForm' . uniqid();
	@endphp
		
	<form id="{{ $formId }}" action="{{ action('Admin\WebhookController@setup', $webhook->uid) }}" method="POST" class="">
		@csrf

		@include('helpers.form_control.webhook', [
			'webhook' => $webhook,
			'formId' => $formId,
			'testUrl' => action('Admin\WebhookController@test', $webhook->uid),
			'tags' => $webhook->getTags(),
		])

		<div class="mt-4">
			<a href="{{ action('Admin\WebhookController@index') }}" class="btn btn-link me-2"
				data-dismiss="modal">{{ trans('messages.cancel') }}</a>
		</div>
		
	</form>

	<script>
		$(() => {
			new WebhookForm({
				form: $('#{{ $formId }}'),
			});
		});

		var WebhookForm = class {
			constructor(options) {
				this.form = options.form;

				this.events();
			}

			getSaveButton() {
				return this.form.find('[data-control="save"]');
			}

			events() {
				// save
				this.getSaveButton().on('click', () => {
					this.save();
				});
			}

			save() {
				if (!this.form[0].reportValidity()) {
					return;
				}

				addMaskLoading();

				$.ajax({
					url: this.url,
					type: 'POST',
					data: this.form.serialize(),
				}).done((res) => {
					// done
					removeMaskLoading();

					//
					new Dialog('alert', {
						message: res.message,
						ok: function() {
							window.location = res.redirect;
						}
					});
				});
			}
		}
	</script>
@endsection