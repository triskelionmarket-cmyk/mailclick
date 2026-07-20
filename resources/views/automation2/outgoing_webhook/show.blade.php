@include('automation2._back')

<h4 class="mb-3">{{ trans('messages.automation.outgoing_webhook') }}</h4>
<p>{{ trans('messages.automation.action.outgoing-webhook.desc') }}</p>

<div>
    <table class="table border">
        <tbody>
            <tr>
                <th width="50%" class="bg-light fw-normal">{{ trans('messages.webhook.request_method') }}</th>
                <td width="50%" class="text-uppercase">
                    {{ $webhook->request_method }}
                </td>
            </tr>
            <tr>
                <th width="50%" class="bg-light fw-normal">{{ trans('messages.webhook.authorization_options') }}</th>
                <td width="50%" class="">
                    {{ trans('messages.webhook.' . $webhook->request_auth_type) }}
                </td>
            </tr>
            <tr>
                <th width="50%" class="bg-light fw-normal">
                    {{ trans('messages.webhook.endpoint_url') }}
                </th>
                <td width="50%" class="">
                    {{ $webhook->request_url }}
                </td>
            </tr>
            <tr>
                <th width="50%" class="bg-light fw-normal">
                    {{ trans('messages.webhook.headers') }}
                </th>
                <td width="50%" class="">
                    @if (count($webhook->getRequestHeaders()))
                        <table class="table m-0">
                            <tbody>
                                @foreach ($webhook->getRequestHeaders() as $header)
                                    <tr>
                                        <th width="50%" class="fw-normal border-0 py-1 px-2 bg-light">
                                            {{ $header['key'] ?? 'N/A' }}:
                                        </th>
                                        <td width="50%" class="text-uppercase border-0 py-1 px-2" style="word-break:break-all;">
                                            {{ $header['value'] ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        {{ trans('messages.webhook.no_headers') }}
                    @endif
                </td>
            </tr>
            <tr>
                <th width="50%" class="bg-light fw-normal">
                    {{ trans('messages.webhook.unified_body_configuration') }}
                </th>
                <td width="50%" class="">
                    {{ trans('messages.webhook.' . $webhook->request_body_type) }}
                </td>
            </tr>
            @if ($webhook->request_body_type == Acelle\Model\Webhook::REQUEST_BODY_TYPE_KEY_VALUE && count($webhook->getRequestBodyParams()))
                <tr>
                    <th width="50%" class="bg-light fw-normal">
                        {{ trans('messages.webhook.body_parameters') }}
                    </th>
                    <td width="50%" class="">
                        @if(count($webhook->getRequestBodyParams()))
                            <table class="table m-0">
                                <tbody>
                                    @foreach ($webhook->getRequestBodyParams() as $param)
                                        <tr>
                                            <th width="50%" class="fw-normal border-0 py-1 px-2 bg-light">
                                                {{ $param['key'] ?? 'N/A' }}:
                                            </th>
                                            <td width="50%" class="text-uppercase border-0 py-1 px-2" style="word-break:break-all;">
                                                {{ $param['value'] ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </td>
                </tr>
            @endif
            @if ($webhook->request_body_type == Acelle\Model\Webhook::REQUEST_BODY_TYPE_PLAIN)
                <tr>
                    <th width="50%" class="bg-light fw-normal">
                        {{ trans('messages.webhook.plain_text') }}
                    </th>
                    <td width="50%" class="">
                        {!! $webhook->request_body_plain !!}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div>
    <button webhook-control="save" type="button" class="btn btn-secondary">
        {{ trans('messages.automation.webhook.edit') }}
    </button>
</div>

<div class="mt-4 d-flex py-3">
    <div>
        <h4 class="mb-2">
            {{ trans('messages.automation.dangerous_zone') }}
        </h4>
        <p class="">
            {{ trans('messages.automation.action.delete.wording') }}                
        </p>
        <div class="mt-3">
            <a data-control="webhook-delete" href="javascript:;" data-confirm="{{ trans('messages.automation.action.delete.confirm') }}"
                class="btn btn-secondary">
                <span class="material-symbols-rounded">delete</span> {{ trans('messages.automation.remove_this_action') }}
            </a>
        </div>
    </div>
</div>

<script>
    $(() => {
        new WebhookManager({
            url: '{!! action('Automation2Controller@outgoingWebhookSetup', [
                'uid' => $automation->uid,
                'webhook_uid' => $webhook->uid,
                'id' => $element->get('id'),
            ]) !!}'
        });
    });

    var WebhookManager = class {
        constructor(options) {
            this.url = options.url;

            //
            this.events();
        }

        getSaveButton() {
            return $('[webhook-control="save"]');
        }

        events() {
            this.getSaveButton().on('click', () => {
                this.showEditPopup();
            });
        }

        showEditPopup() {
            automationPopup.load(this.url);
        }
    }

    $('[data-control="webhook-delete"]').on('click', function(e) {
        e.preventDefault();
        
        var confirm = $(this).attr('data-confirm');
        var dialog = new Dialog('confirm', {
            message: confirm,
            ok: function(dialog) {
                // remove current node
                tree.getSelected().detach();
                
                // save tree
                saveData(function() {
                    // notify
                    notify('success', '{{ trans('messages.notify.success') }}', '{{ trans('messages.automation.action.deteled') }}');
                    
                    // load default sidebar
                    sidebar.load('{{ action('Automation2Controller@settings', $automation->uid) }}');
                });
            },
        });
    });
</script>
