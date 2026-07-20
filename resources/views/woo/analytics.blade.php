@extends('layouts.core.frontend')

@section('title', 'Analiză Magazin & Recomandări')

@section('page_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="font-bold mb-1"><i class="bi bi-graph-up-arrow me-2 text-primary"></i> Analiză Magazin & Recomandări</h1>
            <p class="text-muted mb-0">Tablou de bord analitic, segmentare RFM și gestionare costuri de achiziție per produs.</p>
        </div>
    </div>
@endsection

@section('content')
<!-- KPI Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-currency-dollar fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">VENIT TOTAL COMANDA</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalRevenue, 2) }} RON</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-bag-check fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">TOTAL COMENZI</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalOrders }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">CLIENTI UNICI</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalCustomers }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-star fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">CLV MEDIU ESTIMAT</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($avgClv, 2) }} RON</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RFM Customer Segments & Top Recommended Products -->
<div class="row g-3 mb-4">
    <!-- RFM Segments -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
            <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2 text-primary"></i> Segmentare RFM Clienți</h5>
            <div class="list-group list-group-flush border-0">
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                    <div>
                        <span class="badge bg-success rounded-pill me-2">Champions</span>
                        <small class="text-muted">Scor RFM ≥ 4.2 (Cumpărători recenți & fideli)</small>
                    </div>
                    <span class="fw-bold">{{ $rfmSegments['champions'] }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                    <div>
                        <span class="badge bg-primary rounded-pill me-2">Loyal</span>
                        <small class="text-muted">Scor RFM 3.2 - 4.19 (Clienți constanți)</small>
                    </div>
                    <span class="fw-bold">{{ $rfmSegments['loyal'] }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                    <div>
                        <span class="badge bg-warning text-dark rounded-pill me-2">At Risk</span>
                        <small class="text-muted">Scor RFM 2.0 - 3.19 (Nu au comandat demult)</small>
                    </div>
                    <span class="fw-bold">{{ $rfmSegments['at_risk'] }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                    <div>
                        <span class="badge bg-danger rounded-pill me-2">Lost</span>
                        <small class="text-muted">Scor RFM < 2.0 (Inactivi)</small>
                    </div>
                    <span class="fw-bold">{{ $rfmSegments['lost'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Recommended Products -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
            <h5 class="fw-bold mb-3"><i class="bi bi-award me-2 text-warning"></i> Top Produse Recomandate de Promovat</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produs</th>
                            <th>Preț</th>
                            <th>Scor Recomandare ($P_{score}$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $prod)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $prod->name }}</td>
                                <td>{{ number_format($prod->price, 2) }} RON</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 rounded-pill">
                                        {{ number_format($prod->rfm_score, 2) }} pts
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Rulați prima sincronizare pentru a calcula scorurile de recomandare.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Products Table with Purchase Cost Inline Editor & CSV Import -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-tags me-2 text-primary"></i> Catalog Produse & Cost de Achiziție</h5>
            <span class="text-muted small">Adaugă/Editează costul de achiziție pentru calculul automat de marjă de profit.</span>
        </div>
        <div>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold me-2" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Importă CSV Costuri
            </button>
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
                        <h5 class="modal-title fw-bold" id="importCsvModalLabel"><i class="bi bi-upload text-primary me-2"></i> Importă Costuri de Achiziție (CSV)</h5>
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

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Produs</th>
                    <th>SKU</th>
                    <th>Preț Vânzare</th>
                    <th>Cost Achiziție (RON)</th>
                    <th>Marjă Profit (%)</th>
                    <th>Stoc</th>
                    <th>Acțiune</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr id="prod-row-{{ $product->id }}">
                        <td class="fw-semibold text-dark">{{ $product->name }}</td>
                        <td><code class="text-muted">{{ $product->sku ?: 'N/A' }}</code></td>
                        <td class="fw-bold">{{ number_format($product->price, 2) }} RON</td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm width-120 cost-input" 
                                   data-id="{{ $product->id }}" value="{{ $product->purchase_cost }}">
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success profit-margin-badge-{{ $product->id }}">
                                {{ $product->profit_margin }}%
                            </span>
                        </td>
                        <td>{{ $product->stock_quantity }} buc</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary save-cost-btn" data-id="{{ $product->id }}">
                                <i class="bi bi-save me-1"></i> Salvează
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
    </div>

    <div class="mt-3">
        {{ $products->links() }}
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

        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

        $.ajax({
            url: '/woo/products/' + id + '/purchase-cost',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                purchase_cost: cost
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg text-success"></i> Salvat');
                $('.profit-margin-badge-' + id).text(res.profit_margin);
                setTimeout(function() {
                    btn.html('<i class="bi bi-save me-1"></i> Salvează');
                }, 2000);
            },
            error: function(err) {
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Salvează');
                alert('Eroare la salvarea costului de achiziție.');
            }
        });
    });
});
</script>
@endsection
