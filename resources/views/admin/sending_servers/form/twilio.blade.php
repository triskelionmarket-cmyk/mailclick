@if (!$server->id)
<form id="editServerForm" action="{{ action('Admin\SendingServerController@store', ["type" => request()->type]) }}" method="POST" class="form-validate-jqueryz">
    {{ csrf_field() }}
    <input type="hidden" name="type" value="{{ $server->type }}" />
@else
<form id="editServerForm" enctype="multipart/form-data" action="{{ action('Admin\SendingServerController@update', [$server->uid, $server->type]) }}" method="POST" class="form-validate-jqueryz">
    <input type="hidden" name="_method" value="PATCH">
    {{ csrf_field() }}
@endif

    <div class="mc_section">
        <div class="row">
            <div class="col-md-6">
                <p>{!! trans('messages.sending_server.intro.twilio') !!}</p>

                @include('helpers.form_control', [
                    'type' => 'text',
                    'class' => '',
                    'label' => trans('messages.sending_server.twilio.account_sid'),
                    'name' => 'twilio_account_sid',
                    'value' => $server->twilio_account_sid,
                    'help_class' => 'sending_server',
                    'rules' => $server->getRules(),
                    'disabled' => ($server->id && $errors->isEmpty()),
                ])

                @include('helpers.form_control', [
                    'type' => 'password',
                    'class' => '',
                    'name' => 'twilio_auth_token',
                    'label' => trans('messages.sending_server.twilio.auth_token'),
                    'value' => $server->twilio_auth_token,
                    'help_class' => 'sending_server',
                    'rules' => $server->getRules(),
                    'eye' => true,
                    'disabled' => ($server->id && $errors->isEmpty()),
                ])

                @include('helpers.form_control', [
                    'type' => 'text',
                    'class' => '',
                    'name' => 'twilio_from_number',
                    'label' => trans('messages.sending_server.twilio.from_number'),
                    'value' => $server->twilio_from_number,
                    'help_class' => 'sending_server',
                    'rules' => $server->getRules(),
                    'disabled' => ($server->id && $errors->isEmpty()),
                ])
            </div>
            <div class="col-md-6">
                @if (isset($bigNotices))
                    {!! implode("", $bigNotices) !!}
                @endif
            </div>
        </div>
        <div class="text-left">
            @if ($server->id && Auth::user()->admin->can('test', $server)  && $errors->isEmpty())
                <span class="edit-group">
                    <a
                        href="{{ action('Admin\SendingServerController@testConnection', $server->uid) }}"
                        role="button"
                        class="btn btn-secondary me-2 test-connection-button"
                        mask-title="{{ trans('messages.sending_server.testing') }}"
                    >
                        {{ trans('messages.sending_server.test_connection') }}
                    </a>
                    <a id="SendTestSmsButton"
                        href="{{ action('Admin\SendingServerController@testSms', $server->uid) }}"
                        role="button"
                        class="btn btn-secondary me-2 modal_link"
                        data-in-form="true"
                        link-method="GET"
                    >
                        {{ trans('messages.sending_server.send_a_test_sms') }}
                    </a>
                    <a href="javascript:;" role="button" class="btn btn-link switch-form-toggle">
                        {{ trans('messages.edit') }}
                    </a>
                </span>
                <span class="cancel-group hide">
                    <button class="btn btn-secondary me-2">{{ trans('messages.save') }}</button>
                    <a href="javascript:;" role="button" class="btn btn-link switch-form-toggle">
                        {{ trans('messages.cancel') }}
                    </a>
                </span>
            @else
                <button class="btn btn-secondary me-2">{{ trans('messages.save') }}</button>
                <a href="{{ action('Admin\SendingServerController@index') }}" role="button" class="btn btn-link">
                    {{ trans('messages.cancel') }}
                </a>
            @endif
        </div>
    </div>
</form>
@if ($server->id)
<form action="{{ action('Admin\SendingServerController@config', $server->uid) }}" method="POST" class="form-validate-jqueryz">
    {{ csrf_field() }}
    <div class="mc_section">
        <div class="row">
            <div class="col-md-6">
                <h2 class=" mt-20">{{ trans('messages.sending_servers.configuration_settings') }}</h2>
                <p>
                    {{ trans('messages.sending_servers.configuration_settings.twilio.intro') }}
                </p>

                @include('helpers.form_control', [
                    'type' => 'text',
                    'class' => '',
                    'name' => 'name',
                    'value' => $server->name,
                    'help_class' => 'sending_server',
                    'rules' => $server->getConfigRules(),
                ])

                @include('helpers.form_control', [
                    'type' => 'text',
                    'class' => '',
                    'name' => 'default_from_email',
                    'value' => $server->default_from_email,
                    'help_class' => 'sending_server',
                    'rules' => $server->getConfigRules(),
                ])

                <p>{{ trans('messages.sending_servers.sending_limit.twilio.intro') }}</p>

                <div class="sendind-limit-select-custom" data-url="{{ action('Admin\SendingServerController@sendingLimit', ['uid' => ($server->uid ? $server->uid : 0)]) }}">
                    @include ('admin.sending_servers.form._sending_limit', [
                        'quotaValue' => $server->quota_value,
                        'quotaBase' => $server->quota_base,
                        'quotaUnit' => $server->quota_unit,
                    ])
                </div>
            </div>
        </div>
    </div>
</form>
@endif
