@if ($webhooks->count() > 0)
    <table class="table table-box pml-table mt-2"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        @foreach ($webhooks as $key => $webhook)
            <tr class="position-relative">
                <td width="1%" class="list-check-col">
                    <div class="text-nowrap">
                        <div class="checkbox inline me-1">
                            <label>
                                <input type="checkbox" class="node styled"
                                    name="uids[]"
                                    value="{{ $webhook->uid }}"
                                />
                            </label>
                        </div>
                    </div>
                </td>
                <td>
                    <h5 class="m-0 fw-semibold">
                        <a class="kq_search d-block" href="{{ action('Admin\WebhookController@setup', ["id" => $webhook->uid]) }}">{{ $webhook->name }}</a>
                    </h5>
                    <span class="text-muted2">{{ trans('messages.created_at') }}: {{ Auth::user()->admin->formatDateTime($webhook->created_at, 'datetime_full') }}</span>
                </td>
                <td>
                    <label class="m-0 d-block">
                        {{ trans('messages.webhook.event.' . $webhook->event) }}
                    </label>
                    <span class="text-muted2">{{ trans('messages.webhook.event') }}</span>
                </td>
                <td>
                    <a class="d-block fw-semibold" data-control="webhook-job" href="{{ action('Admin\WebhookController@jobs', $webhook->uid) }}">
                        <span>{{ $webhook->webhookJobs()->count() }}</span>
                    </a>
                    <span class="text-muted2">{{ trans('messages.webhook.runs') }}</span>
                </td>
                <td>
                    <h5 class="m-0" style="line-height: 22px;">
                        <code class="fw-normal" style="white-space:nowrap;text-overflow:ellipsis;max-width:250px;display:block;overflow:hidden;"><span class="text-uppercase">{{ $webhook->request_method }}</span> {{ $webhook->request_url }}</code>
                    </h5>
                    <span class="text-muted2">{{ trans('messages.webhook.desc') }}</span>
                </td>
                <td>
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-{{ $webhook->status }}">{{ trans('messages.webhook.status.' . $webhook->status) }}</span>
                    </span>
                </td>
                <td class="text-end text-nowrap pe-0">
                    <a data-control="webhook-job" href="{{ action('Admin\WebhookController@jobs', ["id" => $webhook->uid]) }}" title="{{ trans('messages.edit') }}" role="button" class="btn btn-secondary btn-icon">
                        <span class="material-symbols-rounded">manage_search</span>
                    </a>
                    @if (Auth::user()->admin->can('delete', $webhook) || Auth::user()->admin->can('disable', $webhook) || Auth::user()->admin->can('enable', $webhook))
                        <div class="btn-group">
                            <button role="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown"></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if (Auth::user()->admin->can('read', $webhook))
                                    <li>
                                        <a class="dropdown-item" href="{{ action('Admin\WebhookController@setup', $webhook->uid) }}">
                                            <span class="material-symbols-rounded">edit</span> {{ trans('messages.edit') }}
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->admin->can('update', $webhook))
                                    <li>
                                        <a class="dropdown-item" data-control="test-webhook" href="{{ action('Admin\WebhookController@test', $webhook->uid) }}">
                                            <span class="material-symbols-rounded">science</span> {{ trans('messages.webhook.test_webhook') }}
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->admin->can('enable', $webhook))
                                    <li>
                                        <a class="dropdown-item list-action-single" link-method="POST" link-confirm="{{ trans('messages.webhook.enable.confirm') }}" href="{{ action('Admin\WebhookController@enable', ["uids" => $webhook->uid]) }}">
                                            <span class="material-symbols-rounded">play_arrow</span> {{ trans('messages.enable') }}
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->admin->can('disable', $webhook))
                                    <li>
                                        <a class="dropdown-item list-action-single" link-method="POST" link-confirm="{{ trans('messages.webhook.enable.confirm') }}" href="{{ action('Admin\WebhookController@disable', ["uids" => $webhook->uid]) }}">
                                            <span class="material-symbols-rounded">hide_source</span> {{ trans('messages.disable') }}
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->admin->can('delete', $webhook))
                                    <li>
                                        <a class="dropdown-item list-action-single" link-method="POST" link-confirm="{{ trans('messages.webhook.delete.confirm') }}" href="{{ action('Admin\WebhookController@delete', ["uids" => $webhook->uid]) }}">
                                            <span class="material-symbols-rounded">delete_outline</span> {{ trans('messages.delete') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', [
        'items' => $webhooks,
    ])

    <script>
        $(() => {
			new TestWebhook({
				links: $('[data-control="test-webhook"]'),
			});

            new WebhookJob({
				links: $('[data-control="webhook-job"]'),
			});
		});

        var TestWebhook = class {
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

        var WebhookJob = class {
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
