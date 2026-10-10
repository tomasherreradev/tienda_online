@extends('themes.xylo.layouts.master')
@section('content')
<div class="container py-5 text-center">
    <h1 class="mb-3">{{ __('store.checkout.order_success') }}</h1>
    <p class="text-muted mb-4">{{ __('store.checkout.success_message') }}</p>
    <a href="{{ route('xylo.home') }}" class="btn btn-primary">{{ __('store.cart.continue_shopping') }}</a>
</div>
@endsection
