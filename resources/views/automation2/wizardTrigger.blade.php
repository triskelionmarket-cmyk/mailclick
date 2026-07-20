@extends('layouts.popup.large')

@section('content')
<div class="row">
	<div class="col-md-12">
		<h2 class="mb-3">{{ trans('messages.automation.automation_trigger') }}</h2>
		<p>{{ trans('messages.automation.trigger.intro') }}</p>

		{{-- Pre-built Templates Section --}}
		@if (!empty($templates))
		<div class="mt-4 mb-2">
			<h5 class="text-muted text-uppercase" style="font-size: 12px; letter-spacing: 1px; font-weight: 600;">
				<i class="material-symbols-rounded me-1"
					style="font-size: 14px; vertical-align: middle;">auto_awesome</i>
				{{ trans('messages.automation.template.use_template') }}
			</h5>
		</div>

		<div class="box-list">
			<div class="box-list">
				@foreach ($templates as $template)
				<a data-control="template-select" class="box-item shadow-sm" data-key="{{ $template['key'] }}" href="{{ action('Automation2Controller@wizardTemplate', [
						'template_key' => $template['key'],
					]) }}">
					<h6 class="d-flex align-items-center text-center justify-content-center">
						<i class="material-symbols-rounded me-2">{{ $template['icon'] }}</i>
						<span>{{ trans('messages.automation.template.' . $template['key']) }}</span>
					</h6>
					<p>{{ trans('messages.automation.template.' . $template['key'] . '.desc') }}</p>
				</a>
				@endforeach
			</div>
		</div>

		<hr class="my-4">

		<div class="mb-2">
			<h5 class="text-muted text-uppercase" style="font-size: 12px; letter-spacing: 1px; font-weight: 600;">
				{{ trans('messages.automation.template.or_start_from_scratch') }}
			</h5>
		</div>
		@endif

		<div class="box-list">
			<div class="box-list">
				@foreach ($types as $type)
				<a data-control="trigger-select" class="box-item trigger-select-but trigger-{{ $type }} shadow-sm"
					data-key="{{ $type }}" href="{{ action('Automation2Controller@wizardTriggerOption', [
								'trigger_type' => $type,
							]) }}">
					@include('automation2.trigger.icons.' . $type)
				</a>
				@endforeach
			</div>
		</div>
	</div>

	<script>
		$(function () {
			$('[data-control="trigger-select"]').on('click', function (e) {
				e.preventDefault();
				var url = $(this).attr('href');
				createAutomationPopup.load(url);
			});

			$('[data-control="template-select"]').on('click', function (e) {
				e.preventDefault();
				var url = $(this).attr('href');
				createAutomationPopup.load(url);
			});
		});
	</script>
	@endsection