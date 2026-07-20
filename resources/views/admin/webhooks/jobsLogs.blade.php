@extends('layouts.popup.large')

@section('content')
    <h2>{{ $webhookJob->webhook->name }}: {{ trans('messages.webhook.logs') }}</h2>

    <div id="WebhookJobsLogsListContainer">
        <div class="d-flex top-list-controls top-sticky-content">
            <div class="me-auto">
                <div class="filter-box">
                    <span class="filter-group">
                        <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                        <select class="select" name="sort_order">
                            <option value="webhook_job_logs.created_at">{{ trans('messages.created_at') }}</option>
                            <option value="webhook_job_logs.updated_at">{{ trans('messages.updated_at') }}</option>
                        </select>
                        <input type="hidden" name="sort_direction" value="desc" />
<button type="button" class="btn btn-light sort-direction" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" role="button" class="btn btn-xs">
                            <span class="material-symbols-rounded desc">sort</span>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <div id="WebhookJobsLogsListContent">
        </div>
    </div>

    <script>
        var WebhookJobsLogs = {
            getList: function() {
                return makeList({
                    url: '{{ action('Admin\WebhookController@jobsLogsList', $webhookJob->uid) }}',
                    container: $('#WebhookJobsLogsListContainer'),
                    content: $('#WebhookJobsLogsListContent')
                });
            }
        };

        $(function() {
            WebhookJobsLogs.getList().load();
        });
    </script>
@endsection
