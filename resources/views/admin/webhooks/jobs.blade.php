@extends('layouts.popup.large')

@section('content')
    <h2>{{ $webhook->name }}: {{ trans('messages.webhook.jobs') }}</h2>

    <div id="WebhookJobsListContainer">
        <div class="d-flex top-list-controls top-sticky-content">
            <div class="me-auto">
                <div class="filter-box">
                    <div class="dropdown list_actions" style="display: none">
                        <button role="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            {{ trans('messages.actions') }} <span class="number"></span><span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" link-method="POST" link-confirm="{{ trans('messages.webhook.enable.confirm') }}" href="{{ action('Admin\WebhookController@enable') }}"><span class="material-symbols-rounded">play_arrow</span> {{ trans('messages.enable') }}</a></li>
                            <li><a class="dropdown-item" link-method="POST" link-confirm="{{ trans('messages.webhook.disable.confirm') }}" href="{{ action('Admin\WebhookController@disable') }}"><span class="material-symbols-rounded">hide_source</span> {{ trans('messages.disable') }}</a></li>
                            <li><a class="dropdown-item" link-method="POST" link-confirm="{{ trans('messages.webhook.delete.confirm') }}" href="{{ action('Admin\WebhookController@delete') }}"><span class="material-symbols-rounded">delete_outline</span> {{ trans('messages.delete') }}</a></li>
                        </ul>
                    </div>
                    <span class="filter-group">
                        <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                        <select class="select" name="sort_order">
                            <option value="webhook_jobs.created_at">{{ trans('messages.created_at') }}</option>
                            <option value="webhook_jobs.updated_at">{{ trans('messages.updated_at') }}</option>
                        </select>
                        <input type="hidden" name="sort_direction" value="desc" />
<button type="button" class="btn btn-light sort-direction" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" role="button" class="btn btn-xs">
                            <span class="material-symbols-rounded desc">sort</span>
                        </button>
                    </span>
                    <span class="text-nowrap">
                        <input type="text" name="keyword" class="form-control search" value="{{ request()->keyword }}" placeholder="{{ trans('messages.type_to_search') }}" />
                        <span class="material-symbols-rounded">search</span>
                    </span>
                </div>
            </div>
        </div>

        <div id="WebhookJobsListContent">
        </div>
    </div>

    <script>
        var WebhooksIndex = {
            getList: function() {
                return makeList({
                    url: '{{ action('Admin\WebhookController@jobsList', $webhook->uid) }}',
                    container: $('#WebhookJobsListContainer'),
                    content: $('#WebhookJobsListContent')
                });
            }
        };

        $(function() {
            WebhooksIndex.getList().load();
        });
    </script>
@endsection
