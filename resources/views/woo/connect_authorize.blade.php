@extends('layouts.core.page')

@section('title', 'Autorizare Conectare Magazin WooCommerce')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 text-center">
                <div class="mb-4">
                    <img src="{{ Setting::get('site_logo_light') ? action('SettingController@file', ['filename' => Setting::get('site_logo_light')]) : asset('images/logo_light.svg') }}" alt="MailClick" style="max-height: 50px;">
                    <div class="my-3 text-muted">
                        <i class="bi bi-link-45deg fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold">Conectare Magazin WooCommerce</h4>
                    <p class="text-muted">Autorizează conectarea magazinului online la contul tău MailClick</p>
                </div>

                <div class="bg-light p-3 rounded-3 text-start mb-4 border">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-shop fs-4 text-secondary me-3"></i>
                        <div>
                            <div class="fw-semibold text-dark">{{ $storeName }}</div>
                            <small class="text-muted">{{ $storeUrl }}</small>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-4 text-secondary me-3"></i>
                        <div>
                            <div class="fw-semibold text-dark">{{ $user->displayName(false) }}</div>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info text-start small mb-4">
                    <i class="bi bi-shield-check me-1"></i> MailClick va primi acces autorizat pentru sincronizarea comenzilor, clienților și a coșurilor abandonate.
                </div>

                <form method="POST" action="{{ action('WooConnectController@approveStore') }}">
                    @csrf
                    <input type="hidden" name="store_url" value="{{ $storeUrl }}">
                    <input type="hidden" name="store_name" value="{{ $storeName }}">
                    <input type="hidden" name="callback_url" value="{{ $callbackUrl }}">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold py-2">
                            <i class="bi bi-check-circle-fill me-2"></i> Autorizează & Conectează
                        </button>
                        <a href="{{ $callbackUrl }}?status=cancelled" class="btn btn-link text-muted text-decoration-none">
                            Anulează
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
