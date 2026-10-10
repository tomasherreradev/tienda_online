@extends('themes.xylo.layouts.auth')

@section('content')
@include('themes.xylo.auth.partials.shell-open', [
    'heroTitle' => __('store.login.welcome_back'),
    'heroLead' => __('store.login.login_description'),
    'heroBadge' => config('app.name'),
])

            <div class="auth-store-card__head text-center">
                <img class="auth-store-logo" src="{{ asset('assets/logotipos/logo.png') }}" width="112" alt="{{ config('app.name') }}">
                <h2 class="auth-store-card__title">{{ __('store.login.login_now') }}</h2>
                <p class="auth-store-card__subtitle">{{ __('store.login.form_subtitle') }}</p>
            </div>

            <form method="POST" action="{{ route('customer.login') }}" class="auth-store-form">
                @csrf
                <div class="auth-field mb-3">
                    <label class="auth-field__label" for="login-email">{{ __('store.login.email') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-regular fa-envelope auth-field__icon" aria-hidden="true"></i>
                        <input id="login-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                               placeholder="{{ __('store.login.email') }}" class="form-control">
                    </div>
                    @error('email') <span class="auth-field__error">{{ $message }}</span> @enderror
                </div>
                <div class="auth-field mb-3">
                    <div class="auth-field__label-row">
                        <label class="auth-field__label mb-0" for="login-password">{{ __('store.login.password') }}</label>
                        <a href="{{ route('customer.password.request') }}" class="auth-link auth-link--sm">{{ __('store.login.forgot_password') }}</a>
                    </div>
                    <div class="auth-field__control">
                        <i class="fa-solid fa-lock auth-field__icon" aria-hidden="true"></i>
                        <input id="login-password" type="password" name="password" autocomplete="current-password" required
                               placeholder="{{ __('store.login.password') }}" class="form-control">
                    </div>
                    @error('password') <span class="auth-field__error">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-submit w-100">{{ __('store.login.login_btn') }}</button>
            </form>

            <p class="auth-store-card__footer text-center">
                {{ __('store.login.dont_have_account') }}
                <a href="{{ route('customer.register') }}" class="auth-link">{{ __('store.login.signup') }}</a>
            </p>

@include('themes.xylo.auth.partials.shell-close')
@endsection
