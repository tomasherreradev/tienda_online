@extends('themes.xylo.layouts.auth')

@section('content')
@include('themes.xylo.auth.partials.shell-open', [
    'heroTitle' => __('store.password.reset_title'),
    'heroLead' => __('store.password.reset_lead'),
    'heroBadge' => config('app.name'),
])

            <div class="auth-store-card__head text-center">
                <div class="auth-store-icon-ring mb-3">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                </div>
                <h2 class="auth-store-card__title">{{ __('store.password.reset_heading') }}</h2>
                <p class="auth-store-card__subtitle">{{ __('store.password.reset_subtitle') }}</p>
            </div>

            <form method="POST" action="{{ route('customer.password.update') }}" class="auth-store-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="auth-field mb-3">
                    <label class="auth-field__label" for="reset-email">{{ __('store.password.email') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-regular fa-envelope auth-field__icon" aria-hidden="true"></i>
                        <input id="reset-email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required
                               class="form-control">
                    </div>
                    @error('email') <span class="auth-field__error">{{ $message }}</span> @enderror
                </div>
                <div class="auth-field mb-3">
                    <label class="auth-field__label" for="reset-password">{{ __('store.password.new_password') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-solid fa-lock auth-field__icon" aria-hidden="true"></i>
                        <input id="reset-password" type="password" name="password" autocomplete="new-password" required
                               placeholder="{{ __('store.password.new_password') }}" class="form-control">
                    </div>
                    @error('password') <span class="auth-field__error">{{ $message }}</span> @enderror
                </div>
                <div class="auth-field mb-4">
                    <label class="auth-field__label" for="reset-password-confirm">{{ __('store.password.confirm_password') }}</label>
                    <div class="auth-field__control">
                        <i class="fa-solid fa-lock auth-field__icon" aria-hidden="true"></i>
                        <input id="reset-password-confirm" type="password" name="password_confirmation" autocomplete="new-password" required
                               placeholder="{{ __('store.password.confirm_password') }}" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-submit w-100">{{ __('store.password.reset_btn') }}</button>
            </form>

            <p class="auth-store-card__footer text-center mb-0">
                <a href="{{ route('customer.login') }}" class="auth-link">{{ __('store.password.back_login') }}</a>
            </p>

@include('themes.xylo.auth.partials.shell-close')
@endsection
