@extends('layouts.core.frontend', [
    'menu' => 'source',
])

@section('title', trans('messages.analytics_recommendations'))

@section('head')
    <script type="text/javascript" src="{{ AppUrl::asset('core/echarts/echarts.min.js') }}"></script>
    <script type="text/javascript" src="{{ AppUrl::asset('core/echarts/dark.js') }}"></script>
@endsection

@section('page_header')
    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li class="breadcrumb-item"><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ action("SourceController@index") }}">{{ trans('messages.stores_connections') }}</a></li>
        </ul>
        <h1>
            <span class="material-symbols-rounded">analytics</span> {{ trans('messages.analytics_recommendations') }}
        </h1>
    </div>
@endsection

@section('content')
    <div id="AnalyticsContainer" class="listing-form top-sticky">
        <div class="d-flex top-list-controls top-sticky-content">
            <div class="me-auto">
                <div class="filter-box">
                    @if(isset($stores) && $stores->count() > 1)
                        <span class="filter-group">
                            <span class="title text-semibold text-muted">{{ trans('messages.source.switch_store') }}:</span>
                            <select class="select" name="store_id" onchange="window.location.href='{{ url('ecommerce/analytics') }}?store_id=' + this.value">
                                @foreach($stores as $st)
                                    <option value="{{ $st->id }}" {{ $st->id == $selectedStore->id ? 'selected' : '' }}>
                                        {{ $st->store_name }}
                                    </option>
                                @endforeach
                            </select>
                        </span>
                    @endif
                    <span class="text-nowrap search-container">
                        <input type="text" name="keyword" class="form-control search" value="{{ request()->keyword }}" placeholder="{{ trans('messages.type_to_search') }}" />
                        <span class="material-symbols-rounded">search</span>
                    </span>
                </div>
            </div>
            <div class="text-end">
                <a href="{{ action('SourceController@sync', ['uid' => \Acelle\Model\Source::where('customer_id', Auth::user()->customer->id)->first()?->uid ?? '']) }}"
                   link-method="POST"
                   class="btn btn-secondary m-icon sync-button me-1">
                    <span class="material-symbols-rounded">sync</span> {{ trans('messages.source.sync') }}
                </a>
                <button type="button" class="btn btn-primary m-icon" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                    <span class="material-symbols-rounded">upload_file</span> {{ trans('messages.woo.btn_import_csv') }}
                </button>
            </div>
        </div>

        {{-- KPI Summary --}}
        <h2 class="mt-4 pt-2">{!! trans('messages.frontend_dashboard_hello', ['name' => Auth::user()->displayName(get_localization_config('show_last_name_first', Auth::user()->customer->getLanguageCode()))]) !!}</h2>
        <p>{{ trans('messages.woo.overview_intro') }}</p>

        <h3 class="mt-5 mb-3">
            <span class="material-symbols-rounded me-2">donut_large</span>
            {{ trans('messages.woo.kpi_title') }}
        </h3>

        <div class="row quota_box">
            <div class="col-12 col-md-6">
                <div class="content-group-sm mb-3">
                    <div class="d-flex mb-2">
                        <label class="fw-600 me-auto">{{ trans('messages.woo.kpi_revenue') }}</label>
                        <div class="pull-right text-semibold">
                            <span class="text-muted">{{ number_format($totalRevenue, 0) }} RON</span>
                        </div>
                    </div>
                    <div class="progress progress-sm" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped bg-{{ $totalRevenue > 100000 ? 'primary' : 'info' }}" style="width: {{ min(100, $totalOrders > 0 ? 77 : 0) }}%"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="content-group-sm mb-3">
                    <div class="d-flex mb-2">
                        <label class="fw-600 me-auto">{{ trans('messages.woo.kpi_orders') }}</label>
                        <div class="pull-right text-semibold">
                            <span class="text-muted">{{ number_format($totalOrders) }} {{ trans('messages.woo.orders_label') }}</span>
                        </div>
                    </div>
                    <div class="progress progress-sm" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped bg-primary" style="width: {{ min(100, $totalOrders > 0 ? 65 : 0) }}%"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="content-group-sm">
                    <div class="d-flex mb-2">
                        <label class="fw-600 me-auto">{{ trans('messages.woo.kpi_customers') }}</label>
                        <div class="pull-right text-semibold">
                            <span class="text-muted">{{ number_format($totalCustomers) }} {{ trans('messages.woo.customers_label') }}</span>
                        </div>
                    </div>
                    <div class="progress progress-sm" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped bg-primary" style="width: {{ min(100, $totalCustomers > 0 ? 45 : 0) }}%"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="content-group-sm">
                    <div class="d-flex mb-2">
                        <label class="fw-600 me-auto">{{ trans('messages.woo.kpi_clv') }}</label>
                        <div class="pull-right text-semibold">
                            <span class="text-muted">{{ number_format($avgClv, 2) }} RON</span>
                        </div>
                    </div>
                    <div class="progress progress-sm" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped bg-{{ $avgClv > 10000 ? 'danger' : 'primary' }}" style="width: {{ min(100, $avgClv > 0 ? 55 : 0) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <h3 class="mt-5 mb-3">
            <span class="material-symbols-rounded me-2">star_half</span>
            {{ trans('messages.woo.detailed_data') }}
        </h3>

        <ul class="nav nav-tabs nav-underline" id="analyticsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="rfm-tab" data-bs-toggle="tab" data-bs-target="#rfm_pane" role="tab">
                    {{ trans('messages.woo.tab_rfm') }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="catalog-tab" data-bs-toggle="tab" data-bs-target="#catalog_pane" role="tab">
                    {{ trans('messages.woo.tab_catalog') }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="recommendations-tab" data-bs-toggle="tab" data-bs-target="#recommendations_pane" role="tab">
                    {{ trans('messages.woo.tab_recommendations') }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders_pane" role="tab">
                    {{ trans('messages.woo.tab_orders') }}
                </a>
            </li>
        </ul>

        <div class="tab-content" id="analyticsTabsContent">

            {{-- TAB 1: RFM --}}
            <div class="tab-pane fade show active" id="rfm_pane" role="tabpanel">
                <div id="rfmChart" style="width:100%;height:280px;margin:20px 0;"></div>

                <div class="row mt-2 mb-4">
                    @php
                        $segments = [
                            ['key' => 'champions', 'label' => trans('messages.woo.seg_champions'), 'desc' => trans('messages.woo.seg_champions_desc'), 'icon' => 'emoji_events', 'color' => '#28a745'],
                            ['key' => 'loyal',     'label' => trans('messages.woo.seg_loyal'),     'desc' => trans('messages.woo.seg_loyal_desc'),     'icon' => 'loyalty',      'color' => '#007bff'],
                            ['key' => 'at_risk',   'label' => trans('messages.woo.seg_at_risk'),   'desc' => trans('messages.woo.seg_at_risk_desc'),   'icon' => 'warning',      'color' => '#ffc107'],
                            ['key' => 'lost',      'label' => trans('messages.woo.seg_lost'),      'desc' => trans('messages.woo.seg_lost_desc'),      'icon' => 'person_off',   'color' => '#dc3545'],
                        ];
                    @endphp
                    @foreach($segments as $seg)
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card px-0 shadow-sm">
                                <div class="card-body pt-2">
                                    <div class="d-flex align-items-center pt-1">
                                        <span class="material-symbols-rounded me-2" style="font-size:22px;color:{{ $seg['color'] }};">{{ $seg['icon'] }}</span>
                                        <label class="panel-title text-semibold my-0 fw-600">{{ $seg['label'] }}</label>
                                    </div>
                                    <h4 class="no-margin text-bold mt-2">{{ $rfmSegments[$seg['key']] }}</h4>
                                    <span class="text-muted">{{ $seg['desc'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <h3 class="mt-4 mb-3">
                    <span class="material-symbols-rounded me-2">campaign</span>
                    {{ trans('messages.woo.winback_title') }}
                </h3>

                @if(count($winbackCustomers) > 0)
                    <table class="table table-box pml-table mt-2" current-page="1">
                        @foreach($winbackCustomers as $key => $cust)
                            <tr>
                                <td width="1%">
                                    <div class="product-image-list mr-3">
                                        <span class="material-symbols-rounded" style="font-size:28px;color:#999;">person</span>
                                    </div>
                                </td>
                                <td>
                                    <a class="kq_search fw-600 d-block list-title" href="javascript:void(0)">
                                        {{ $cust['first_name'] }} {{ $cust['last_name'] }}
                                    </a>
                                    <span class="text-muted d-block mt-1">{{ $cust['email'] }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">{{ $cust['city'] ?? '—' }}</h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_city') }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">{{ $cust['orders_count'] }}</h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_orders') }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">{{ number_format($cust['total_spent'], 0) }} RON</h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_total_spent') }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">{{ $cust['rfm_recency'] }} {{ trans('messages.woo.days') }}</h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_recency') }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ action('CampaignController@selectType') }}" role="button" class="btn btn-secondary m-icon">
                                        <span class="material-symbols-rounded me-1">mark_email_unread</span> {{ trans('messages.woo.btn_send') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <div class="empty-list">
                        <span class="material-symbols-rounded">auto_awesome</span>
                        <span class="line-1">{{ trans('messages.woo.winback_empty') }}</span>
                    </div>
                @endif
            </div>

            {{-- TAB 2: Catalog --}}
            <div class="tab-pane fade" id="catalog_pane" role="tabpanel">
                @if($products->count() > 0)
                    <table class="table table-box pml-table mt-2" current-page="{{ empty(request()->page) ? 1 : request()->page }}">
                        @foreach($products as $key => $product)
                            @php $margin = $product->profit_margin; @endphp
                            <tr>
                                <td width="1%">
                                    <div class="product-image-list mr-3">
                                        <span class="material-symbols-rounded" style="font-size:28px;color:#999;">inventory_2</span>
                                    </div>
                                </td>
                                <td width="30%">
                                    <h5 class="no-margin text-normal"><span class="kq_search">{{ $product->name }}</span></h5>
                                    <span class="text-muted d-block mt-2">SKU: {{ $product->sku ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">{{ number_format($product->price, 2) }} RON</h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_sale_price') }}</span>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm cost-input"
                                           style="max-width: 110px; height: 33px;"
                                           data-id="{{ $product->id }}" value="{{ $product->purchase_cost }}">
                                    <span class="text-muted d-block mt-1" style="font-size:12px;">{{ trans('messages.woo.col_purchase_cost') }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num profit-margin-badge-{{ $product->id }}">
                                        @if($margin >= 35)
                                            <span class="text-success">{{ $margin }}%</span>
                                        @elseif($margin >= 20)
                                            <span style="color:#ffc107;">{{ $margin }}%</span>
                                        @else
                                            <span class="text-danger">{{ $margin }}%</span>
                                        @endif
                                    </h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_margin') }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">{{ $product->stock_quantity }}</h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_stock') }}</span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-secondary m-icon save-cost-btn" data-id="{{ $product->id }}">
                                        <span class="material-symbols-rounded me-1">save</span> {{ trans('messages.woo.btn_save') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    @include('elements/_per_page_select', ["items" => $products])
                @else
                    <div class="empty-list">
                        <span class="material-symbols-rounded">category</span>
                        <span class="line-1">{{ trans('messages.woo.no_products') }}</span>
                    </div>
                @endif
            </div>

            {{-- TAB 3: Recommendations --}}
            <div class="tab-pane fade" id="recommendations_pane" role="tabpanel">
                @if($topProducts->count() > 0)
                    <ul class="modern-listing mt-0 top-border-none">
                        @foreach($topProducts as $num => $prod)
                            <li>
                                <div class="row">
                                    <div class="col-sm-5 col-md-5">
                                        <div class="d-flex align-items-center">
                                            <i class="number d-inline-block me-3">{{ $num + 1 }}</i>
                                            <div>
                                                <h6 class="mt-0 mb-0 text-semibold">{{ $prod->name }}</h6>
                                                <p class="mb-0">SKU: {{ $prod->sku }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-md-2 text-left">
                                        <h5 class="m-0 text-bold">{{ number_format($prod->price, 2) }} RON</h5>
                                        <span class="text-muted">{{ trans('messages.woo.col_price') }}</span>
                                    </div>
                                    <div class="col-sm-2 col-md-2 text-left">
                                        <h5 class="m-0 text-bold">{{ number_format($prod->rfm_score, 2) }}</h5>
                                        <span class="text-muted">{{ trans('messages.woo.col_rfm_score') }}</span>
                                    </div>
                                    <div class="col-sm-3 col-md-3 text-end">
                                        <a href="{{ action('CampaignController@selectType') }}" class="btn btn-secondary m-icon">
                                            <span class="material-symbols-rounded me-1">campaign</span> {{ trans('messages.woo.btn_promote') }}
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="empty-list">
                        <span class="material-symbols-rounded">auto_awesome</span>
                        <span class="line-1">{{ trans('messages.woo.no_recommendations') }}</span>
                    </div>
                @endif
            </div>

            {{-- TAB 4: Recent Orders --}}
            <div class="tab-pane fade" id="orders_pane" role="tabpanel">
                @if($recentOrders->count() > 0)
                    <table class="table table-box pml-table mt-2" current-page="1">
                        @foreach($recentOrders as $key => $ord)
                            <tr>
                                <td width="1%">
                                    <div class="product-image-list mr-3">
                                        <span class="material-symbols-rounded" style="font-size:28px;color:#999;">receipt_long</span>
                                    </div>
                                </td>
                                <td>
                                    <a class="kq_search fw-600 d-block list-title" href="javascript:void(0)">
                                        {{ $ord->order_number ?: '#' . $ord->woo_order_id }}
                                    </a>
                                    <span class="text-muted d-block mt-1">
                                        {{ $ord->billing_first_name }} {{ $ord->billing_last_name }}
                                        @if($ord->customer_email) — {{ $ord->customer_email }} @endif
                                    </span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">{{ number_format($ord->total, 2) }} {{ $ord->currency ?: 'RON' }}</h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_value') }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">
                                        @if($ord->status == 'completed')
                                            <span class="text-success">{{ trans('messages.woo.status_completed') }}</span>
                                        @elseif($ord->status == 'processing')
                                            <span style="color:#007bff;">{{ trans('messages.woo.status_processing') }}</span>
                                        @elseif($ord->status == 'cancelled')
                                            <span class="text-danger">{{ trans('messages.woo.status_cancelled') }}</span>
                                        @else
                                            {{ ucfirst($ord->status) }}
                                        @endif
                                    </h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.woo.col_status') }}</span>
                                </td>
                                <td>
                                    <h5 class="no-margin stat-num">
                                        {{ $ord->created_at ? Auth::user()->customer->formatDateTime($ord->created_at, 'datetime_full') : 'N/A' }}
                                    </h5>
                                    <span class="text-muted d-block mt-2">{{ trans('messages.created_at') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <div class="empty-list">
                        <span class="material-symbols-rounded">auto_awesome</span>
                        <span class="line-1">{{ trans('messages.woo.no_orders') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- CSV Import Modal --}}
    <div class="modal fade" id="importCsvModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ action('\Acelle\Http\Controllers\WooAnalyticsController@importPurchaseCosts') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="store_id" value="{{ $selectedStore->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title fw-600">
                            <span class="material-symbols-rounded me-2">upload_file</span>
                            {{ trans('messages.woo.csv_modal_title') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="text-muted">{!! trans('messages.woo.csv_modal_desc') !!}</p>
                        <div class="form-group">
                            <label class="fw-600">{{ trans('messages.woo.csv_select_file') }}</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv, .txt" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ trans('messages.woo.btn_import') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <br><br>

    <script>
        $(document).ready(function() {
            $('.sync-button').on('click', function() {
                addMaskLoading('{{ trans('messages.source.importing_product') }}');
            });

            $('.save-cost-btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var id = btn.data('id');
                var input = $('.cost-input[data-id="' + id + '"]');
                var cost = input.val();

                btn.prop('disabled', true).html('<span class="material-symbols-rounded">sync</span>');

                $.ajax({
                    url: '/woo/products/' + id + '/purchase-cost',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', purchase_cost: cost },
                    success: function(res) {
                        btn.prop('disabled', false).html('<span class="material-symbols-rounded me-1">check</span> {{ trans('messages.woo.saved') }}');
                        $('.profit-margin-badge-' + id).html('<span class="text-success">' + res.profit_margin + '</span>');
                        setTimeout(function() {
                            btn.html('<span class="material-symbols-rounded me-1">save</span> {{ trans('messages.woo.btn_save') }}');
                        }, 2000);
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html('<span class="material-symbols-rounded me-1">save</span> {{ trans('messages.woo.btn_save') }}');
                        notify({ type: 'danger', message: '{{ trans('messages.woo.cost_save_error') }}' });
                    }
                });
            });

            @if(isset($rfmSegments))
                var rfmChart = echarts.init(document.getElementById('rfmChart'), ECHARTS_THEME);
                rfmChart.setOption({
                    tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
                    legend: { bottom: '0%', left: 'center' },
                    series: [{
                        name: '{{ trans('messages.woo.rfm_chart_name') }}',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        itemStyle: { borderRadius: 6, borderColor: '#fff', borderWidth: 2 },
                        label: { show: false },
                        emphasis: { label: { show: true, fontSize: 14, fontWeight: 'bold' } },
                        labelLine: { show: false },
                        data: [
                            { value: {{ $rfmSegments['champions'] }}, name: '{{ trans('messages.woo.seg_champions') }}', itemStyle: { color: '#28a745' } },
                            { value: {{ $rfmSegments['loyal'] }}, name: '{{ trans('messages.woo.seg_loyal') }}', itemStyle: { color: '#007bff' } },
                            { value: {{ $rfmSegments['at_risk'] }}, name: '{{ trans('messages.woo.seg_at_risk') }}', itemStyle: { color: '#ffc107' } },
                            { value: {{ $rfmSegments['lost'] }}, name: '{{ trans('messages.woo.seg_lost') }}', itemStyle: { color: '#dc3545' } }
                        ]
                    }]
                });
                $(window).on('resize', function() { rfmChart.resize(); });
            @endif
        });
    </script>
@endsection
