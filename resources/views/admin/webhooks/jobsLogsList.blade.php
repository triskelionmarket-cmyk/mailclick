@if ($webhookJobLogs->count() > 0)
    <table class="table table-box pml-table mt-2"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        <tr class="text-nowrap">
            <th class="ps-0">{{ trans('messages.created_at') }}</th>
            <th>{{ trans('messages.webhook.params') }}</th>
            <th>{{ trans('messages.webhook.http_code') }}</th>
            <th>{{ trans('messages.webhook.request_details') }}</th>
            <th>{{ trans('messages.webhook.reponse_content') }}</th>
            <th>{{ trans('messages.webhook.reponse_error') }}</th>
        </tr>
        @foreach ($webhookJobLogs as $key => $webhookJobLog)
            <tr class="position-relative">
                <td>
                    <label class="m-0 d-block">
                        {{ Auth::user()->admin->formatDateTime($webhookJobLog->created_at, 'datetime_full') }}
                    </label>
                </td>
                <td>
                    @foreach ($webhookJobLog->webhookJob->getParams() as $key => $value)
                        <code class="m-0 d-block text-dark">
                            <strong>{{ $key }}:</strong> {{ $value }}
                        </code>
                    @endforeach
                </td>
                <td>
                    <code class="m-0 d-block text-dark">
                        {{ $webhookJobLog->response_http_code }}
                    </code>
                </td>
                <td>
                    <code data-control="more-log-popup" class="m-0 d-block clamp-3 text-dark" style="max-width: 250px;">
                        {{ $webhookJobLog->request_details }}
                    </code>
                </td>
                <td>
                    <code data-control="more-log-popup" class="m-0 d-block clamp-3 text-dark" style="max-width: 250px;">
                        {{ $webhookJobLog->response_content }}
                    </code>
                </td>
                <td>
                    <code class="m-0 d-block clamp-3" style="max-width: 250px;">
                        {{ $webhookJobLog->response_error ?? trans('messages.general.n_a') }}
                    </code>
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', [
        'items' => $webhookJobLogs,
    ])

    <script>
        $(function() {
            new MoreLogPopup({
                controls: $('[data-control="more-log-popup"]'),
            })
        });

        var MoreLogPopup = class {
            constructor(options) {
                this.controls = options.controls;

                this.events();
            }

            events() {
                this.controls.on('click', function(e) {
                    e.preventDefault();

                    var popup = new Popup();
                    var content = $(this).html();

                    popup.loadHtml(`
                        <div class="modal-dialog shadow modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center" style="overflow: auto;">
                                    `+content+`
                                </div>
                            </div>
                        </div>
                    `);
                });
            }
        }
    </script>
@elseif (!empty(request()->keyword) || !empty(request()->filters["type"]))
    <div class="empty-list">
        <span class="material-symbols-rounded">dns</span>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <span class="material-symbols-rounded">dns</span>
        <span class="line-1">
            {{ trans('messages.webhook.empty') }}
        </span>
    </div>
@endif
