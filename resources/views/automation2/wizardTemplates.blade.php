@extends('layouts.popup.large')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2 class="mb-3">{{ trans('messages.automation.templates') }}</h2>
        <p>{{ trans('messages.automation.templates.intro') }}</p>

        <div class="box-list mt-3">
            <div class="box-list mt-5">
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

        <div class="mt-4 text-center">
            <a href="{{ action('Automation2Controller@wizardTrigger') }}" data-control="back-to-triggers"
                class="btn btn-outline-secondary">
                <i class="material-symbols-rounded me-1">arrow_back</i>
                {{ trans('messages.automation.template.or_start_from_scratch') }}
            </a>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('[data-control="template-select"]').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            createAutomationPopup.load(url);
        });

        $('[data-control="back-to-triggers"]').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            createAutomationPopup.load(url);
        });
    });
</script>
@endsection