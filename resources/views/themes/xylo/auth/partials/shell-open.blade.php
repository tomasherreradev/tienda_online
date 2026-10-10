@php
    $heroTitle = $heroTitle ?? '';
    $heroLead = $heroLead ?? null;
    $heroBadge = $heroBadge ?? config('app.name');
@endphp
<div class="auth-store-shell">
    <div class="auth-store-hero">
        <a href="{{ route('xylo.home') }}" class="auth-store-back auth-store-back--hero">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>{{ __('store.product_detail.home') }}</span>
        </a>
        @if ($heroBadge)
            <p class="auth-hero-badge">{{ $heroBadge }}</p>
        @endif
        <h1>{{ $heroTitle }}</h1>
        @if ($heroLead)
            <p class="auth-hero-lead auth-hero-lead--desktop">{{ $heroLead }}</p>
        @endif
        <div class="auth-hero-deco" aria-hidden="true">
            <img src="{{ asset('assets/logotipos/logo.png') }}" alt="">
        </div>
        <p class="auth-hero-foot d-none d-lg-block">{{ __('store.auth.copyright') }}</p>
    </div>
    <div class="auth-store-form-panel">
        <div class="auth-store-card">
