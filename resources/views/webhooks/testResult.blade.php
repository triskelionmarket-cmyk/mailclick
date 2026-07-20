<div class="mb-4">
    <div>
        <h4 class="fw-semibold">{{ trans('messages.webhook.test_request') }}</h4>
        <div class="p-3 bg-light border">
            <pre>{{ json_encode($webhookJobLog->getRequestDetails(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>
</div>

<div class="">
    <div>
        <h4 class="fw-semibold">{{ trans('messages.webhook.test_output') }}</h4>
        <div class="p-3 bg-light border">
            <div test-control="output">
                <div class="">
                    <div>
                        <div class="">
                            <div class="">
                                <p><strong>{{ trans('messages.webhook.http_repsonse_code') }}:</strong> {{ $webhookJobLog->response_http_code }}</p>
                                <p class="mb-2"><strong>{{ trans('messages.webhook.repsonse_body') }}:</strong></p>
                                <pre style="width: 100%;
height: auto;
white-space: inherit;">{{ $webhookJobLog->response_content }}</pre>
                                @if ($webhookJobLog->response_error)
                                    <p><strong>{{ trans('messages.webhook.error') }}:</strong></p>
                                    <pre class='bg-danger text-white p-3'>{{ $webhookJobLog->response_error }}</pre>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>