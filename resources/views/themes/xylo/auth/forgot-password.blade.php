@extends('themes.xylo.layouts.auth')

@section('content')
@include('themes.xylo.auth.partials.shell-open', [
    'heroTitle' => __('store.password.forgot_title'),
    'heroLead' => __('store.password.forgot_lead'),
    'heroBadge' => config('app.name'),
])

            <div class="auth-store-card__head text-center">
                <div class="auth-store-icon-ring mb-3">
                    <i class="fa-solid fa-key" aria-hidden="true"></i>
                </div>
                <h2 class="auth-store-card__title">{{ __('store.password.forgot_heading') }}</h2>
                <p class="auth-store-card__subtitle">{{ __('store.password.forgot_subtitle') }}</p>
            </div>

            @if (session('status'))
                <div class="auth-alert auth-alert--success" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('customer.password.email') }}" class="auth-store-form">
                @csrf
                <div class="auth-field mb-4">
                    <label class="auth-field__label" for="forgot-email">{{ __('store.password.email') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-regular fa-envelope auth-field__icon" aria-hidden="true"></i>
                        <input id="forgot-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                               placeholder="{{ __('store.password.email') }}" class="form-control">
                    </div>
                    @error('email') <span class="auth-field__error">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-submit w-100">{{ __('store.password.send_link') }}</button>
            </form>

            <p class="auth-store-card__footer text-center mb-0">
                <a href="{{ route('customer.login') }}" class="auth-link"><i class="fa-solid fa-arrow-left me-1"></i>{{ __('store.password.back_login') }}</a>
            </p>

@include('themes.xylo.auth.partials.shell-close')
@endsection
