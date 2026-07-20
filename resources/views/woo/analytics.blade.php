@extends('layouts.core.frontend')

@section('title', 'Analiză Magazin & Recomandări Inteligente')

@section('page_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1 d-flex align-items-center text-dark">
                <span class="material-symbols-rounded text-primary fs-2 me-2">insights</span>
                Analiză Magazin & Recomandări Inteligente
            </h1>
            <p class="text-muted mb-0 fs-6">
                Monitorizare RFM, Valoare CLV Clienți, Marje de Profit și Automatizări MailClick în Timp Real.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <!-- Store Selector -->
            @if(isset($stores) && $stores->count() > 1)
                <form action="{{ url('ecommerce/analytics') }}" method="GET" class="d-inline-block">
                    <select name="store_id" class="form-select fw-semibold border-secondary shadow-sm" onchange="this.form.submit()">
                        @foreach($stores as $st)
                            <option value="{{ $st->id }}" {{ $st->id == $selectedStore->id ? 'selected' : '' }}>
                                🏪 {{ $st->store_name }} ({{ $st->store_url }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <!-- Sync Button -->
            <a href="{{ action('SourceController@sync', ['uid' => \Acelle\Model\Source::where('customer_id', Auth::user()->customer->id)->first()?->uid ?? '1']) }}" 
               class="btn btn-outline-primary fw-bold d-flex align-items-center shadow-sm">
                <span class="material-symbols-rounded me-1">sync</span>
                Sincronizare Date
            </a>

            <!-- Import CSV Button -->
            <button type="button" class="btn btn-secondary fw-bold d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                <span class="material-symbols-rounded me-1">upload_file</span>
                Import Costuri (CSV)
            </button>
        </div>
    </div>
@endsection

@section('content')
<!-- KPI Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <span class="material-symbols-rounded fs-2">payments</span>
                </div>
                <div>
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Venit Total</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalRevenue, 2) }} <small class="fs-6 text-muted">RON</small></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <span class="material-symbols-rounded fs-2">shopping_bag</span>
                </div>
                <div>
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Total Comenzi</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalOrders) }} <small class="fs-6 text-muted">comenzi</small></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <span class="material-symbols-rounded fs-2">group</span>
                </div>
                <div>
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">Clienți B2B & Retail</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalCustomers) }} <small class="fs-6 text-muted">clienți</small></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <span class="material-symbols-rounded fs-2">trending_up</span>
                </div>
                <div>
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">CLV Mediu Estimat</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($avgClv, 2) }} <small class="fs-6 text-muted">RON</small></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-4 shadow-sm border" id="analyticsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-item nav-link active fw-bold d-flex align-items-center px-4 py-2 rounded-3" id="rfm-tab" data-bs-toggle="pill" data-bs-target="#rfm-pane" type="button" role="tab">
            <span class="material-symbols-rounded me-2">pie_chart</span>
            Segmentare RFM Clienți
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-item nav-link fw-bold d-flex align-items-center px-4 py-2 rounded-3" id="products-tab" data-bs-toggle="pill" data-bs-target="#products-pane" type="button" role="tab">
            <span class="material-symbols-rounded me-2">inventory_2</span>
            Catalog Produse & Marje
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-item nav-link fw-bold d-flex align-items-center px-4 py-2 rounded-3" id="recommendations-tab" data-bs-toggle="pill" data-bs-target="#recommendations-pane" type="button" role="tab">
            <span class="material-symbols-rounded me-2">recommend</span>
            Recomandări Cross-Sell
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-item nav-link fw-bold d-flex align-items-center px-4 py-2 rounded-3" id="orders-tab" data-bs-toggle="pill" data-bs-target="#orders-pane" type="button" role="tab">
            <span class="material-symbols-rounded me-2">receipt_long</span>
            Istoric Comenzi Recente
        </button>
    </li>
</ul>

<div class="tab-content" id="analyticsTabsContent">
    <!-- TAB 1: RFM Customer Segments -->
    <div class="tab-pane fade show active" id="rfm-pane" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 border-start border-4 border-success shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-pill mb-2">Champions</span>
                            <h4 class="fw-bold mb-0">{{ $rfmSegments['champions'] }} clienți</h4>
                            <small class="text-muted">Recență & Frecvență Mare</small>
                        </div>
                        <span class="material-symbols-rounded text-success fs-1 opacity-50">workspace_premium</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 border-start border-4 border-primary shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-pill mb-2">Loyal Customers</span>
                            <h4 class="fw-bold mb-0">{{ $rfmSegments['loyal'] }} clienți</h4>
                            <small class="text-muted">Cumpărători Constanți</small>
                        </div>
                        <span class="material-symbols-rounded text-primary fs-1 opacity-50">verified_user</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 border-start border-4 border-warning shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-warning bg-opacity-10 text-dark fw-bold px-3 py-1 rounded-pill mb-2">At Risk</span>
                            <h4 class="fw-bold mb-0">{{ $rfmSegments['at_risk'] }} clienți</h4>
                            <small class="text-muted">Necomandați de >60 zile</small>
                        </div>
                        <span class="material-symbols-rounded text-warning fs-1 opacity-50">warning</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 border-start border-4 border-danger shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-1 rounded-pill mb-2">Lost / Inactive</span>
                            <h4 class="fw-bold mb-0">{{ $rfmSegments['lost'] }} clienți</h4>
                            <small class="text-muted">Risc Mare de Churn</small>
                        </div>
                        <span class="material-symbols-rounded text-danger fs-1 opacity-50">person_off</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Win-back Target Customers Table -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1 d-flex align-items-center">
                        <span class="material-symbols-rounded me-2 text-warning">campaign</span>
                        Clienți Risc Inactivitate - Recomandări Automatizare MailClick
                    </h5>
                    <p class="text-muted small mb-0">Clienți cu valoare istorică mare (CLV) care nu au mai comandat recent. Declanșează campanii automate de recuperare.</p>
                </div>
                <a href="{{ action('CampaignController@create') }}" class="btn btn-primary fw-bold d-flex align-items-center shadow-sm">
                    <span class="material-symbols-rounded me-1">send</span>
                    Creează Campanie Win-Back
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Client / Companie B2B</th>
                            <th>Oraș</th>
                            <th>Comenzi Totale</th>
                            <th>Total Cheltuit</th>
                            <th>Zile Inactivitate</th>
                            <th>CLV Estimat</th>
                            <th class="text-end">Acțiune</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($winbackCustomers as $cust)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-light text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($cust['first_name'], 0, 1) . substr($cust['last_name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $cust['first_name'] }} {{ $cust['last_name'] }}</div>
                                            <small class="text-muted">{{ $cust['email'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $cust['city'] ?? 'România' }}</span></td>
                                <td class="fw-bold">{{ $cust['orders_count'] }} comenzi</td>
                                <td class="fw-bold text-dark">{{ number_format($cust['total_spent'], 2) }} RON</td>
                                <td><span class="badge bg-warning bg-opacity-10 text-dark fw-semibold">{{ $cust['rfm_recency'] }} zile</span></td>
                                <td class="fw-bold text-success">{{ number_format($cust['clv_estimated'], 2) }} RON</td>
                                <td class="text-end">
                                    <a href="{{ action('CampaignController@create') }}?email={{ urlencode($cust['email']) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                        <span class="material-symbols-rounded me-1 fs-6 align-middle">mark_email_unread</span>
                                        Trimite Oferta
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Nu există clienți inactivi de mare valoare în acest moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: Product Catalog & Profit Margin -->
    <div class="tab-pane fade" id="products-pane" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold mb-1 d-flex align-items-center">
                        <span class="material-symbols-rounded me-2 text-primary">inventory_2</span>
                        Catalog Produse & Marje de Profit
                    </h5>
                    <span class="text-muted small">Adaugă/Editează costul de achiziție per produs pentru calculul automat de marjă netă.</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form action="{{ url('ecommerce/analytics') }}" method="GET" class="d-flex me-2">
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm me-2" placeholder="Caută produs sau SKU...">
                        <button type="submit" class="btn btn-sm btn-secondary">Caută</button>
                    </form>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 fw-bold d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                        <span class="material-symbols-rounded me-1 fs-6">upload_file</span>
                        Import CSV
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Denumire Produs</th>
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
                                $badgeClass = $margin >= 35 ? 'bg-success' : ($margin >= 20 ? 'bg-warning text-dark' : 'bg-danger');
                            @endphp
                            <tr id="prod-row-{{ $product->id }}">
                                <td class="fw-bold text-dark">{{ $product->name }}</td>
                                <td><code class="text-muted">{{ $product->sku ?: 'N/A' }}</code></td>
                                <td class="fw-bold text-primary">{{ number_format($product->price, 2) }} RON</td>
                                <td>
                                    <div class="input-group input-group-sm" style="max-width: 140px;">
                                        <input type="number" step="0.01" min="0" class="form-control cost-input fw-semibold" 
                                               data-id="{{ $product->id }}" value="{{ $product->purchase_cost }}">
                                        <span class="input-group-text">RON</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill profit-margin-badge-{{ $product->id }}">
                                        {{ $margin }}%
                                    </span>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $product->stock_quantity }} buc</span></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary save-cost-btn fw-bold" data-id="{{ $product->id }}">
                                        <span class="material-symbols-rounded me-1 fs-6 align-middle">save</span>
                                        Salvează
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Niciun produs găsit în catalog.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 3: Recommendations & Cross-Sell -->
    <div class="tab-pane fade" id="recommendations-pane" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold mb-3 d-flex align-items-center">
                <span class="material-symbols-rounded me-2 text-warning">star</span>
                Top Produse Recomandate pentru Promovare MailClick
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produs</th>
                            <th>SKU</th>
                            <th>Preț Vânzare</th>
                            <th>Scor Recomandare RFM ($P_{score}$)</th>
                            <th class="text-end">Acțiune</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $prod)
                            <tr>
                                <td class="fw-bold text-dark">{{ $prod->name }}</td>
                                <td><code class="text-muted">{{ $prod->sku }}</code></td>
                                <td class="fw-bold text-primary">{{ number_format($prod->price, 2) }} RON</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 rounded-pill fw-bold">
                                        ⭐ {{ number_format($prod->rfm_score, 2) }} pts
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ action('CampaignController@create') }}" class="btn btn-sm btn-outline-primary fw-bold">
                                        <span class="material-symbols-rounded me-1 fs-6 align-middle">campaign</span>
                                        Promovează în MailClick
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Rulați sincronizarea pentru a genera produsele recomandate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: Recent Orders History -->
    <div class="tab-pane fade" id="orders-pane" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold mb-3 d-flex align-items-center">
                <span class="material-symbols-rounded me-2 text-primary">receipt_long</span>
                Istoric Comenzi Recente (Sincronizate)
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Număr Comandă</th>
                            <th>Client / Email</th>
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
                                    <div class="fw-semibold">{{ $ord->billing_first_name }} {{ $ord->billing_last_name }}</div>
                                    <small class="text-muted">{{ $ord->customer_email }}</small>
                                </td>
                                <td class="fw-bold text-dark">{{ number_format($ord->total, 2) }} {{ $ord->currency }}</td>
                                <td><span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-pill">Finalizată</span></td>
                                <td class="text-muted">{{ $ord->created_at ? $ord->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nu există comenzi sincronizate recent.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ action('\Acelle\Http\Controllers\WooAnalyticsController@importPurchaseCosts') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="store_id" value="{{ $selectedStore->id }}">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="importCsvModalLabel">
                        <span class="material-symbols-rounded text-primary me-2 align-middle">upload_file</span>
                        Importă Costuri de Achiziție (CSV)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted small">Încarcă un fișier CSV care conține coloana <code>purchase_cost</code> și coloana <code>sku</code> (sau <code>woo_product_id</code>).</p>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Selectează fișierul CSV</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv, .txt" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Renunță</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Importă</button>
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

        btn.prop('disabled', true).html('<span class="material-symbols-rounded me-1 fs-6 align-middle">sync</span> Salvare...');

        $.ajax({
            url: '/woo/products/' + id + '/purchase-cost',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                purchase_cost: cost
            },
            success: function(res) {
                btn.prop('disabled', false).html('<span class="material-symbols-rounded me-1 fs-6 align-middle text-success">check</span> Salvat');
                $('.profit-margin-badge-' + id).text(res.profit_margin);
                setTimeout(function() {
                    btn.html('<span class="material-symbols-rounded me-1 fs-6 align-middle">save</span> Salvează');
                }, 2000);
            },
            error: function(err) {
                btn.prop('disabled', false).html('<span class="material-symbols-rounded me-1 fs-6 align-middle">save</span> Salvează');
                alert('Eroare la salvarea costului de achiziție.');
            }
        });
    });
});
</script>
@endsection
