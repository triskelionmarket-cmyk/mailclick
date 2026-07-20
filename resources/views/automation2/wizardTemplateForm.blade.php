@extends('layouts.popup.small')

@section('content')
<div class="row">
    <div class="col-md-12">
        <form id="automationTemplateCreate" action="{{ action('Automation2Controller@wizardTemplate') }}" method="POST"
            class="form-validate-jqueryz">
            {{ csrf_field() }}

            <input type="hidden" name="template_key" value="{{ $template_key }}" />

            @php
            $templateInfo = collect($templates)->firstWhere('key', $template_key);
            @endphp

            <h1 class="mb-20">
                <i class="material-symbols-rounded me-2">{{ $templateInfo['icon'] ?? 'auto_awesome' }}</i>
                {{ trans('messages.automation.template.' . $template_key) }}
            </h1>

            <p class="mb-3">{{ trans('messages.automation.template.' . $template_key . '.desc') }}</p>

            <p class="mb-10">{{ trans('messages.automation.name_your_automation') }}</p>

            <div class="row mb-4">
                <div class="col-md-8">
                    @include('helpers.form_control', [
                    'type' => 'text',
                    'class' => '',
                    'label' => '',
                    'name' => 'name',
                    'value' => $automation->name,
                    'help_class' => 'automation',
                    'rules' => $automation->rules(),
                    ])
                </div>
            </div>

            @include('helpers.form_control', [
            'type' => 'select',
            'class' => 'required',
            'label' => trans('messages.automation.choose_list'),
            'name' => 'mail_list_uid',
            'value' => '',
            'options' => Auth::user()->customer->local()->readCache('MailListSelectOptions', []),
            'include_blank' => trans('messages.automation.choose_list.placeholder'),
            'rules' => ['mail_list_uid' => 'required'],
            ])

            <div class="text-center mt-4">
                <button class="btn btn-secondary mt-20">{{ trans('messages.automation.get_started') }}</button>
            </div>

        </form>

    </div>
</div>

<script>
    $('#automationTemplateCreate').submit(function (e) {
        e.preventDefault();

        var form = $(this);
        var url = form.attr('action');

        // loading effect
        createAutomationPopup.loading();

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            globalError: false,
            statusCode: {
                // validate error
                400: function (res) {
                    createAutomationPopup.loadHtml(res.responseText);
                }
            },
            success: function (res) {
                createAutomationPopup.hide();

                addMaskLoading(res.message, function () {
                    setTimeout(function () {
                        window.location = res.url;
                    }, 1000);
                });
            }
        }).always(function () {
            createAutomationPopup.unmask();
        });
    });
</script>
@endsection