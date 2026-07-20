@if ($webhookJobs->count() > 0)
    <table class="table table-box pml-table mt-2"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        <tr>
            <th class="ps-0">{{ trans('messages.created_at') }}</th>
            <th>{{ trans('messages.webhook.params') }}</th>
            <th>{{ trans('messages.webhook.http_code') }}</th>
            {{-- <th>{{ trans('messages.webhook.request_details') }}</th> --}}
            <th>{{ trans('messages.webhook.reponse_content') }}</th>
            {{-- <th>{{ trans('messages.webhook.reponse_error') }}</th> --}}
            <th>{{ trans('messages.webhook.retries') }}</th>
            <th>{{ trans('messages.status') }}</th>
            <th width="1%"></th>
        </tr>
        @foreach ($webhookJobs as $key => $webhookJob)
            <tr class="position-relative">
                <td>
                    <label class="m-0 d-block">
                        {{ Auth::user()->admin->formatDateTime($webhookJob->created_at, 'datetime_full') }}
                    </label>
                </td>
                <td>
                    @foreach ($webhookJob->getParams() as $key => $value)
                        <code class="m-0 d-block text-dark">
                            <strong>{{ $key }}:</strong> {{ $value }}
                        </code>
                    @endforeach
                </td>
                @if ($webhookJob->getLatestLog())
                    <td>
                        <code class="m-0 d-block text-dark">
                            {{ $webhookJob->getLatestLog()->response_http_code }}
                        </code>
                    </td>
                    {{-- <td>
                        <code class="m-0 d-block clamp-3 text-dark" style="max-width: 250px;">
                            {{ $webhookJob->getLatestLog()->request_details }}
                        </code>
                    </td> --}}
                    <td>
                        <code data-control="more-popup" class="m-0 d-block clamp-3 text-dark" style="max-width: 250px;">
                            {{ $webhookJob->getLatestLog()->response_content }}
                        </code>
                    </td>
                    {{-- <td>
                        <code class="m-0 d-block clamp-3 text-dark" style="max-width: 250px;">
                            {{ $webhookJob->getLatestLog()->response_error ?? trans('messages.general.n_a') }}
                        </code>
                    </td> --}}
                @else
                    <td>
                        <code class="m-0 d-block text-dark">
                            {{ trans('messages.general.n_a') }}
                        </code>
                    </td>
                    {{-- <td>
                        <code class="m-0 d-block clamp-3 text-dark" style="max-width: 250px;">
                            {{ trans('messages.general.n_a') }}
                        </code>
                    </td> --}}
                    <td>
                        <code class="m-0 d-block clamp-3 text-dark" style="max-width: 250px;">
                            {{ trans('messages.general.n_a') }}
                        </code>
                    </td>
                    {{-- <td>
                        <code class="m-0 d-block clamp-3 text-dark" style="max-width: 250px;">
                            {{ trans('messages.general.n_a') }}
                        </code>
                    </td> --}}
                @endif
                <td>
                    <label class="m-0 d-block fw-semibold">
                        {{ $webhookJob->retries }}
                    </label>
                </td>
                <td>
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-{{ $webhookJob->status }}">{{ trans('messages.webhook.job.status.' . $webhookJob->status) }}</span>
                    </span>
                </td>
                <td>
                    <a data-control="webhook-log" href="{{ action('Admin\WebhookController@jobsLogs', ["id" => $webhookJob->uid]) }}" role="button" class="btn btn-light btn-icon">
                        <span class="material-symbols-rounded">zoom_in</span>
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', [
        'items' => $webhookJobs,
    ])

    <script>
        $(function() {
            new MorePopup({
                controls: $('[data-control="more-popup"]'),
            })
        });

        var MorePopup = class {
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

    <script>
        $(() => {
            new WebhookJobLog({
                links: $('[data-control="webhook-log"]'),
            });
        });

        var WebhookJobLog = class {
            constructor(options) {
                this.links = options.links;

                this.events();
            }

            events() {
                this.links.on('click', function(e) {
                    e.preventDefault();

                    var popup = new Popup({url: $(this).attr('href')});
                    popup.load();
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
