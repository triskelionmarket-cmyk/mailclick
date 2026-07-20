@extends('layouts.core.frontend')

@section('title', 'Analiză Magazin WooCommerce')

@section('page_header')
    <div class="page-title">
        <h1 class="font-bold"><i class="bi bi-shop me-2"></i> Analiză Magazin WooCommerce</h1>
        <p class="text-muted">Conectează un magazin WooCommerce pentru a debloca analitica avansată, segmentarea RFM și recomandările de produse.</p>
    </div>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 text-center p-5 my-4">
    <div class="mb-3">
        <i class="bi bi-cart-plus text-primary display-3"></i>
    </div>
    <h3 class="fw-bold mb-2">Niciun magazin WooCommerce conectat</h3>
    <p class="text-muted mb-4 max-w-md mx-auto">Descarcă și instalează plugin-ul <strong>MailClick Connect</strong> în magazinul tău WordPress pentru sincronizare automată 1-Click.</p>
    <div>
        <a href="{{ asset('plugins/mailclick-connect.zip') }}" class="btn btn-primary btn-lg rounded-3 fw-bold px-4 me-2" download>
            <i class="bi bi-download me-2"></i> Descarcă Plugin WordPress (.zip)
        </a>
    </div>
</div>
@endsection
