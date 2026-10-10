@extends('themes.xylo.layouts.auth')

@section('content')
@include('themes.xylo.auth.partials.shell-open', [
    'heroTitle' => __('store.register.signup_now'),
    'heroLead' => __('store.register.signup_description'),
    'heroBadge' => __('store.register.hello'),
])

            <div class="auth-store-card__head text-center">
                <img class="auth-store-logo" src="{{ asset('assets/logotipos/logo.png') }}" width="112" alt="{{ config('app.name') }}">
                <h2 class="auth-store-card__title">{{ __('store.register.welcome_back') }}</h2>
                <p class="auth-store-card__subtitle">{{ __('store.register.form_subtitle') }}</p>
            </div>

            <form method="POST" action="{{ route('customer.register') }}" class="auth-store-form">
                @csrf
                <div class="auth-field mb-3">
                    <label class="auth-field__label" for="register-name">{{ __('store.register.name') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-regular fa-user auth-field__icon" aria-hidden="true"></i>
                        <input id="register-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required
                               placeholder="{{ __('store.register.name') }}" class="form-control">
                    </div>
                    @error('name') <span class="auth-field__error">{{ $message }}</span> @enderror
                </div>
                <div class="auth-field mb-3">
                    <label class="auth-field__label" for="register-email">{{ __('store.register.email') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-regular fa-envelope auth-field__icon" aria-hidden="true"></i>
                        <input id="register-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                               placeholder="{{ __('store.register.email') }}" class="form-control">
                    </div>
                    @error('email') <span class="auth-field__error">{{ $message }}</span> @enderror
                </div>
                <div class="auth-field mb-3">
                    <label class="auth-field__label" for="register-password">{{ __('store.register.password') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-solid fa-lock auth-field__icon" aria-hidden="true"></i>
                        <input id="register-password" type="password" name="password" autocomplete="new-password" required
                               placeholder="{{ __('store.register.password') }}" class="form-control">
                    </div>
                    @error('password') <span class="auth-field__error">{{ $message }}</span> @enderror
                </div>
                <div class="auth-field mb-4">
                    <label class="auth-field__label" for="register-password-confirm">{{ __('store.register.confirm_password') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-solid fa-lock auth-field__icon" aria-hidden="true"></i>
                        <input id="register-password-confirm" type="password" name="password_confirmation" autocomplete="new-password" required
                               placeholder="{{ __('store.register.confirm_password') }}" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-submit w-100">{{ __('store.register.signup_btn') }}</button>
            </form>

            <p class="auth-store-card__footer text-center">
                {{ __('store.register.already_account') }}
                <a href="{{ route('customer.login') }}" class="auth-link">{{ __('store.register.login_here') }}</a>
            </p>

@include('themes.xylo.auth.partials.shell-close')
@endsection
