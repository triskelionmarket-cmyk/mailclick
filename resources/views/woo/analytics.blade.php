@extends('layouts.core.frontend', [
    'menu' => 'source',
])

@section('title', 'Analiză Magazin & Recomandări')

@section('page_header')
    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li class="breadcrumb-item"><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ action("SourceController@index") }}">{{ trans('messages.sources') }}</a></li>
        </ul>
        <h1>
            <span class="material-symbols-rounded me-2">analytics</span>
            <span class="text-semibold">Analiză Magazin & Recomandări</span>
        </h1>
    </div>
@endsection

@section('content')
<!-- Filter & Action Controls -->
<div class="d-flex top-list-controls top-sticky-content mb-4 align-items-center">
    <div class="me-auto">
        <div class="filter-box">
            @if(isset($stores) && $stores->count() > 1)
                <span class="filter-group me-3">
                    <span class="title text-semibold text-muted">Magazin:</span>
                    <select class="select form-select d-inline-block w-auto" name="store_id" onchange="window.location.href='{{ url('ecommerce/analytics') }}?store_id=' + this.value">
                        @foreach($stores as $st)
                            <option value="{{ $st->id }}" {{ $st->id == $selectedStore->id ? 'selected' : '' }}>
                                {{ $st->store_name }} ({{ $st->store_url }})
                            </option>
                        @endforeach
                    </select>
                </span>
            @endif
        </div>
    </div>
    <div class="text-end">
        <a href="{{ action('SourceController@sync', ['uid' => \Acelle\Model\Source::where('customer_id', Auth::user()->customer->id)->first()?->uid ?? '1']) }}" 
           class="btn btn-secondary m-icon me-1">
            <span class="material-symbols-rounded me-1">sync</span> Sincronizare Date
        </a>
        <button type="button" class="btn btn-primary m-icon" data-bs-toggle="modal" data-bs-target="#importCsvModal">
            <span class="material-symbols-rounded me-1">upload_file</span> Import Costuri CSV
        </button>
    </div>
</div>

<!-- Stat KPI Cards (Acelle Dashboard Box Style) -->
<div class="row mb-4">
    <div class="col-12 col-md-3">
        <div class="card px-3 py-3 shadow-sm border-0 mb-2">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <span class="material-symbols-rounded text-primary" style="font-size: 38px;">payments</span>
                </div>
                <div>
                    <span class="text-muted text-semibold small d-block">VENIT TOTAL</span>
                    <h3 class="stat-num m-0 fw-bold text-dark">{{ number_format($totalRevenue, 2) }} RON</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card px-3 py-3 shadow-sm border-0 mb-2">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <span class="material-symbols-rounded text-success" style="font-size: 38px;">shopping_bag</span>
                </div>
                <div>
                    <span class="text-muted text-semibold small d-block">TOTAL COMENZI</span>
                    <h3 class="stat-num m-0 fw-bold text-dark">{{ number_format($totalOrders) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card px-3 py-3 shadow-sm border-0 mb-2">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <span class="material-symbols-rounded text-info" style="font-size: 38px;">group</span>
                </div>
                <div>
                    <span class="text-muted text-semibold small d-block">CLIENȚI B2B & RETAIL</span>
                    <h3 class="stat-num m-0 fw-bold text-dark">{{ number_format($totalCustomers) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card px-3 py-3 shadow-sm border-0 mb-2">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <span class="material-symbols-rounded text-warning" style="font-size: 38px;">trending_up</span>
                </div>
                <div>
                    <span class="text-muted text-semibold small d-block">CLV MEDIU ESTIMAT</span>
                    <h3 class="stat-num m-0 fw-bold text-dark">{{ number_format($avgClv, 2) }} RON</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs (Acelle Native Nav Underline Style) -->
<ul class="nav nav-tabs nav-underline mb-4" id="analyticsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active fw-600" id="rfm-tab" data-bs-toggle="tab" data-bs-target="#rfm_pane" role="tab" aria-controls="rfm_pane" aria-selected="true">
            <span class="material-symbols-rounded me-2">pie_chart</span> Segmentare RFM Clienți
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link fw-600" id="catalog-tab" data-bs-toggle="tab" data-bs-target="#catalog_pane" role="tab" aria-controls="catalog_pane" aria-selected="false">
            <span class="material-symbols-rounded me-2">inventory_2</span> Catalog Produse & Marje
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link fw-600" id="recommendations-tab" data-bs-toggle="tab" data-bs-target="#recommendations_pane" role="tab" aria-controls="recommendations_pane" aria-selected="false">
            <span class="material-symbols-rounded me-2">recommend</span> Recomandări Cross-Sell
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link fw-600" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders_pane" role="tab" aria-controls="orders_pane" aria-selected="false">
            <span class="material-symbols-rounded me-2">history</span> Istoric Comenzi Recente
        </a>
    </li>
</ul>

<div class="tab-content" id="analyticsTabsContent">
    <!-- TAB 1: RFM Customer Segments -->
    <div class="tab-pane fade show active" id="rfm_pane" role="tabpanel" aria-labelledby="rfm-tab">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-0 border-start border-4 border-success">
                    <span class="label label-flat bg-success d-inline-block w-auto mb-2">Champions</span>
                    <h3 class="fw-bold m-0">{{ $rfmSegments['champions'] }} clienți</h3>
                    <span class="text-muted small">Cumpărători recenți & fideli</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-0 border-start border-4 border-primary">
                    <span class="label label-flat bg-primary d-inline-block w-auto mb-2">Loyal Customers</span>
                    <h3 class="fw-bold m-0">{{ $rfmSegments['loyal'] }} clienți</h3>
                    <span class="text-muted small">Clienți constanți</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-0 border-start border-4 border-warning">
                    <span class="label label-flat bg-warning d-inline-block w-auto mb-2 text-dark">At Risk</span>
                    <h3 class="fw-bold m-0">{{ $rfmSegments['at_risk'] }} clienți</h3>
                    <span class="text-muted small">Inactivi de >60 zile</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-0 border-start border-4 border-danger">
                    <span class="label label-flat bg-danger d-inline-block w-auto mb-2">Lost / Inactive</span>
                    <h3 class="fw-bold m-0">{{ $rfmSegments['lost'] }} clienți</h3>
                    <span class="text-muted small">Risc mare de pierdere</span>
                </div>
            </div>
        </div>

        <!-- Win-back Customer List (Acelle Table Style) -->
        <h4 class="mt-4 mb-3 fw-bold">
            <span class="material-symbols-rounded me-2 text-warning">campaign</span>
            Clienți Țintă pentru Campanie Win-Back MailClick
        </h4>

        <table class="table table-box pml-table mt-2">
            <thead>
                <tr>
                    <th>Client / Companie</th>
                    <th>Oraș</th>
                    <th>Comenzi</th>
                    <th>Total Cheltuit</th>
                    <th>Recență</th>
                    <th>CLV Estimat</th>
                    <th class="text-end">Acțiune</th>
                </tr>
            </thead>
            <tbody>
                @forelse($winbackCustomers as $cust)
                    <tr>
                        <td>
                            <a class="kq_search fw-600 d-block list-title" href="javascript:void(0)">
                                {{ $cust['first_name'] }} {{ $cust['last_name'] }}
                            </a>
                            <span class="text-muted d-block small">{{ $cust['email'] }}</span>
                        </td>
                        <td><span class="label label-flat bg-light text-dark">{{ $cust['city'] ?? 'România' }}</span></td>
                        <td><span class="fw-bold">{{ $cust['orders_count'] }}</span> comenzi</td>
                        <td class="fw-bold text-dark">{{ number_format($cust['total_spent'], 2) }} RON</td>
                        <td><span class="label label-flat bg-warning text-dark">{{ $cust['rfm_recency'] }} zile</span></td>
                        <td class="fw-bold text-success">{{ number_format($cust['clv_estimated'], 2) }} RON</td>
                        <td class="text-end">
                            <a href="{{ action('CampaignController@create') }}?email={{ urlencode($cust['email']) }}" class="btn btn-outline-primary btn-sm m-icon">
                                <span class="material-symbols-rounded">mark_email_unread</span> Trimite Oferta
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Nu există clienți inactivi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- TAB 2: Catalog Produse & Profit Margin -->
    <div class="tab-pane fade" id="catalog_pane" role="tabpanel" aria-labelledby="catalog-tab">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0">
                <span class="material-symbols-rounded me-2 text-primary">inventory_2</span>
                Catalog Produse & Marje de Profit
            </h4>
            <div>
                <form action="{{ url('ecommerce/analytics') }}" method="GET" class="d-inline-block">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm d-inline-block w-auto" placeholder="Căutare după nume sau SKU...">
                    <button type="submit" class="btn btn-sm btn-secondary">Caută</button>
                </form>
            </div>
        </div>

        <table class="table table-box pml-table mt-2">
            <thead>
                <tr>
                    <th>Produs</th>
                    <th>SKU</th>
                    <th>Preț Vânzare</th>
                    <th>Cost Achiziție (RON)</th>
                    <th>Marjă Profit (%)</th>
                    <th>Stoc</th>
                    <th class="text-end">Acțiune</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $margin = $product->profit_margin;
                        $labelClass = $margin >= 35 ? 'bg-success' : ($margin >= 20 ? 'bg-warning text-dark' : 'bg-danger');
                    @endphp
                    <tr>
                        <td>
                            <span class="kq_search fw-600 d-block list-title">{{ $product->name }}</span>
                        </td>
                        <td><code class="text-muted">{{ $product->sku ?: 'N/A' }}</code></td>
                        <td class="fw-bold text-dark">{{ number_format($product->price, 2) }} RON</td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm cost-input" style="max-width: 120px;"
                                   data-id="{{ $product->id }}" value="{{ $product->purchase_cost }}">
                        </td>
                        <td>
                            <span class="label label-flat {{ $labelClass }} profit-margin-badge-{{ $product->id }}">
                                {{ $margin }}%
                            </span>
                        </td>
                        <td><span class="text-muted">{{ $product->stock_quantity }} buc</span></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary save-cost-btn m-icon" data-id="{{ $product->id }}">
                                <span class="material-symbols-rounded">save</span> Salvează
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Niciun produs găsit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>

    <!-- TAB 3: Recommendations -->
    <div class="tab-pane fade" id="recommendations_pane" role="tabpanel" aria-labelledby="recommendations-tab">
        <h4 class="mb-3 fw-bold">
            <span class="material-symbols-rounded me-2 text-warning">star</span>
            Top Produse Recomandate pentru Campanii MailClick
        </h4>

        <table class="table table-box pml-table mt-2">
            <thead>
                <tr>
                    <th>Produs</th>
                    <th>SKU</th>
                    <th>Preț Vânzare</th>
                    <th>Scor Recomandare ($P_{score}$)</th>
                    <th class="text-end">Acțiune</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $prod)
                    <tr>
                        <td><span class="fw-600 list-title">{{ $prod->name }}</span></td>
                        <td><code class="text-muted">{{ $prod->sku }}</code></td>
                        <td class="fw-bold">{{ number_format($prod->price, 2) }} RON</td>
                        <td>
                            <span class="label label-flat bg-primary">
                                ⭐ {{ number_format($prod->rfm_score, 2) }} pts
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ action('CampaignController@create') }}" class="btn btn-sm btn-outline-primary m-icon">
                                <span class="material-symbols-rounded">campaign</span> Promovează în MailClick
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Rulați prima sincronizare.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- TAB 4: Recent Orders -->
    <div class="tab-pane fade" id="orders_pane" role="tabpanel" aria-labelledby="orders-tab">
        <h4 class="mb-3 fw-bold">
            <span class="material-symbols-rounded me-2 text-primary">history</span>
            Istoric Comenzi Recente
        </h4>

        <table class="table table-box pml-table mt-2">
            <thead>
                <tr>
                    <th>Număr Comandă</th>
                    <th>Client</th>
                    <th>Valoare Totală</th>
                    <th>Status</th>
                    <th>Dată Comandă</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $ord)
                    <tr>
                        <td><span class="fw-bold text-primary">{{ $ord->order_number }}</span></td>
                        <td>
                            <span class="fw-600 d-block">{{ $ord->billing_first_name }} {{ $ord->billing_last_name }}</span>
                            <small class="text-muted">{{ $ord->customer_email }}</small>
                        </td>
                        <td class="fw-bold text-dark">{{ number_format($ord->total, 2) }} {{ $ord->currency }}</td>
                        <td><span class="label label-flat bg-success">Finalizată</span></td>
                        <td class="text-muted">{{ $ord->created_at ? $ord->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Nu există comenzi recente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ action('\Acelle\Http\Controllers\WooAnalyticsController@importPurchaseCosts') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="store_id" value="{{ $selectedStore->id }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="importCsvModalLabel">
                        <span class="material-symbols-rounded text-primary me-2">upload_file</span>
                        Importă Costuri de Achiziție (CSV)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted small">Încarcă un fișier CSV cu coloana <code>purchase_cost</code> și <code>sku</code> (sau <code>woo_product_id</code>).</p>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Selectează fișierul CSV</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv, .txt" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Renunță</button>
                    <button type="submit" class="btn btn-primary fw-bold">Importă</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.save-cost-btn').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var input = $('.cost-input[data-id="' + id + '"]');
        var cost = input.val();

        btn.prop('disabled', true).html('<span class="material-symbols-rounded fs-6 align-middle">sync</span>');

        $.ajax({
            url: '/woo/products/' + id + '/purchase-cost',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                purchase_cost: cost
            },
            success: function(res) {
                btn.prop('disabled', false).html('<span class="material-symbols-rounded fs-6 align-middle text-success">check</span> Salvat');
                $('.profit-margin-badge-' + id).text(res.profit_margin + '%');
                setTimeout(function() {
                    btn.html('<span class="material-symbols-rounded me-1">save</span> Salvează');
                }, 2000);
            },
            error: function(err) {
                btn.prop('disabled', false).html('<span class="material-symbols-rounded me-1">save</span> Salvează');
                alert('Eroare la salvarea costului.');
            }
        });
    });
});
</script>
@endsection
