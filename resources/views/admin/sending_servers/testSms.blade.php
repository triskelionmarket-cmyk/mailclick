@extends('layouts.popup.small')

@section('title')
    {{ trans('messages.test_sending_server_sms') }}
@endsection

@section('content')
    <form id="TestSmsForm" action="" method="POST" class="form-validate-jquery">
        {{ csrf_field() }}
        @include('helpers.form_control.phone', [
            'name' => 'to_number',
            'label' => trans('messages.to_number'),
            'value' => '',
            'help_class' => 'sending_server',
            'rules' => ['to_number' => 'required'],
            'attributes' => ['class' => '']
        ])
        @include('helpers.form_control', [
            'type' => 'textarea',
            'class' => '',
            'label' => trans('messages.content'),
            'name' => 'content',
            'value' => '',
            'help_class' => 'sending_server',
            'rules' => ['content' => 'required']
        ])
        <div class="mt-4 text-left">
            <button type="submit" class="btn btn-secondary me-1">
                {{ trans('messages.send') }}
            </button>
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.close') }}</button>
        </div>
    </form>

<script>
    $(function() {
        $('#TestSmsForm').on('submit', function(e) {
            var helperInput = $('[name="to_number_Helper"]');
            var hiddenInput = $('[name="to_number"]');
            if (helperInput.length && hiddenInput.length) {
                // Folosește metoda oficială pentru a obține instanța
                var iti = window.intlTelInputGlobals && window.intlTelInputGlobals.getInstance
                    ? window.intlTelInputGlobals.getInstance(helperInput[0])
                    : null;
                if (iti) {
                    hiddenInput.val(iti.getNumber());
                } else {
                    // fallback: pune valoarea brută
                    hiddenInput.val(helperInput.val());
                }
            }
            e.preventDefault();
            if ($(this).valid()) {
                TestSms.run();
            }
            return false;
        });
    });

    var TestSms = {
        url: '{{ action('Admin\SendingServerController@testSms', $server->uid) }}',
        getData: function() {
            return $('#TestSmsForm').serialize();
        },
        run: function() {
            addMaskLoading();
            $.ajax({
                url: this.url,
                type: 'POST',
                data: this.getData(),
                globalError: false
            }).done(function(response) {
                new Dialog('alert', {
                    title: LANG_SUCCESS,
                    message: response.message,
                });
                removeMaskLoading();
            }).fail(function(jqXHR){
                new Dialog('alert', {
                    title: LANG_ERROR,
                    message: JSON.parse(jqXHR.responseText).message,
                });
                removeMaskLoading();
            });
        }
    };
</script>
@endsection