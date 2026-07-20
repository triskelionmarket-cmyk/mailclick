@extends('layouts.core.frontend')

@section('title', 'Analiză Magazin & Recomandări')

@section('page_header')
    <div class="page-title">
        <h1 class="font-bold"><i class="bi bi-shop me-2"></i> Analiză Magazin & Recomandări</h1>
        <p class="text-muted">Conectează un magazin e-commerce pentru a debloca analitica avansată, segmentarea RFM și recomandările de produse.</p>
    </div>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 text-center p-5 my-4">
    <div class="mb-3">
        <i class="bi bi-cart-plus text-primary display-3"></i>
    </div>
    <h3 class="fw-bold mb-2">Niciun magazin e-commerce conectat</h3>
    <p class="text-muted mb-4 max-w-md mx-auto">Conectează magazinul tău din secțiunea <strong>Stores & Connections</strong> pentru sincronizare automată a produselor, comenzilor și clienților.</p>
    <div>
        <a href="{{ action('SourceController@create') }}" class="btn btn-primary btn-lg rounded-3 fw-bold px-4 me-2">
            <i class="bi bi-plus-circle me-2"></i> Adaugă Conexiune Magazin
        </a>
    </div>
</div>
@endsection
