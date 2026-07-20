@php
/**
 * Twilio SMS Test Form
 */
@endphp

@extends('layouts.popup.medium')

@section('title')
    {{ trans('messages.sending_server.test_sms') }}
@endsection

@section('content')
    <form id="testSmsForm" action="{{ action('SendingServerController@testSendSms', $server->uid) }}" method="POST" class="form-validate-jquery">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="to_number">{{ trans('messages.to_phone') }}</label>
                    <input type="text" name="to_number" class="form-control" value="" required placeholder="{{ trans('messages.phone_number_placeholder') }}">
                    <small class="form-text text-muted">{{ trans('messages.test_sms_to_number_help') }}</small>
                </div>
                <div class="form-group">
                    <label for="message">{{ trans('messages.message') }}</label>
                    <textarea name="message" class="form-control" rows="4" required>{{ trans('messages.test_sms_content') }}</textarea>
                    <small class="form-text text-muted">{{ trans('messages.test_sms_content_help') }}</small>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2 test-sms-button">
                <span class="material-symbols-rounded">send</span> {{ trans('messages.send') }}
            </button>
            <button type="button" class="btn btn-default close-button">{{ trans('messages.close') }}</button>
        </div>
    </form>

    <script>
        // Test SMS sending
        $(function() {
            var testSmsForm = $('#testSmsForm').submit(function(e) {
                e.preventDefault();
                
                var url = $(this).attr('action');
                var data = $(this).serialize();
                
                testSmsForm.addClass('loading');
                $('.test-sms-button').addClass('disabled').attr('disabled', true);
                
                // Send test email
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    statusCode: {
                        // validate error
                        400: function (res) {
                            testSmsForm.removeClass('loading');
                            $('.test-sms-button').removeClass('disabled').attr('disabled', false);
                            
                            // notification
                            notify({
                                title: "{{ trans('messages.notify.error') }}",
                                message: res.responseJSON.message,
                                type: 'error'
                            });
                        }
                    },
                    success: function (response) {
                        testSmsForm.removeClass('loading');
                        $('.test-sms-button').removeClass('disabled').attr('disabled', false);
                        
                        if (response.status == 'success') {
                            // notify
                            notify({
                                title: "{{ trans('messages.notify.success') }}",
                                message: response.message
                            });
                        } else {
                            // notify
                            notify({
                                title: "{{ trans('messages.notify.error') }}",
                                message: response.message,
                                type: 'error'
                            });
                        }
                    }
                });
            });
            
            $('.close-button').on('click', function() {
                SendingServerTestPopup.hide();
            });
        });
    </script>
@endsection
