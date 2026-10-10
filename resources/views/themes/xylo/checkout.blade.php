@extends('themes.xylo.layouts.master')
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"> 
@endsection
@section('content')
    @php $currency = activeCurrency(); @endphp
    <div class="store-page checkout-page pb-5">
    <section class="store-page-hero">
        <div class="container">
            <nav class="store-breadcrumbs">
                <a href="{{ route('xylo.home') }}">{{ __('store.checkout.breadcrumb_home') }}</a>
                <i class="fa fa-angle-right"></i>
                <a href="{{ route('cart.view') }}">{{ __('store.cart.breadcrumb_cart') }}</a>
                <i class="fa fa-angle-right"></i>
                <span>{{ __('store.checkout.breadcrumb_checkout') }}</span>
            </nav>
            <h1>{{ __('store.checkout.breadcrumb_checkout') }}</h1>
        </div>
    </section>

    <div class="pt-3">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <form id="checkout-form" method="POST" action="{{ route('checkout.process') }}">
                        @csrf

                        <!-- Shipping Information -->
                        <div class="shipping_info">
                            <h3 class="cart-heading">{{ __('store.checkout.shipping_information') }}</h3>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="first_name" class="form-control" placeholder="{{ __('store.checkout.first_name') }}" required>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="last_name" class="form-control" placeholder="{{ __('store.checkout.last_name') }}" required>
                                </div>
                            </div>
                            <input type="hidden" name="country" value="{{ config('store.country_name') }}">
                            <div class="row">
                                <div class="col-md-12 mt-3">
                                    <input type="text" name="address" class="form-control" placeholder="{{ __('store.checkout.address') }}" required>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <input type="text" name="suite" class="form-control" placeholder="{{ __('store.checkout.suite') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="city" class="form-control" placeholder="{{ __('store.checkout.city') }}" required>
                                </div>
                                <div class="col-md-3 mt-3">
                                    <select name="state" class="form-select" required>
                                        <option value="">{{ __('store.checkout.select_state') }}</option>
                                        @foreach (config('store.provinces') as $code => $provinceName)
                                            <option value="{{ $code }}">{{ $provinceName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mt-3">
                                    <input type="text" name="zipcode" class="form-control" placeholder="{{ __('store.checkout.zipcode') }}" required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label>
                                    <input type="checkbox" name="use_as_billing" value="1" checked>{{ __('store.checkout.use_as_billing') }}
                                </label>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="shipping_info">
                            <h3 class="cart-heading mt-5">{{ __('store.checkout.contact_information') }}</h3>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <input type="email" name="email" class="form-control" placeholder="{{ __('store.checkout.email') }}" required>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="phone" class="form-control" placeholder="{{ __('store.checkout.phone') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="shipping_info mt-5">
                            <h3 class="cart-heading">{{ __('store.checkout.payment_method') }}</h3>

                            @php
                                $defaultGateway = $paymentGateways->firstWhere('code', 'mercadopago')?->code
                                    ?? $paymentGateways->first()?->code;
                            @endphp
                            @foreach($paymentGateways as $gateway)
                                <div class="form-check mt-2">
                                    <input type="radio" name="gateway" value="{{ $gateway->code }}"
                                        id="gateway-{{ $gateway->id }}" required
                                        @checked($gateway->code === $defaultGateway)>
                                    <label for="gateway-{{ $gateway->id }}">
                                        {{ $gateway->name }}
                                        @if($gateway->code === 'mercadopago')
                                            <small class="text-muted d-block">{{ __('store.checkout.mercadopago_hint') }}</small>
                                        @endif
                                    </label>
                                </div>

                                @if($gateway->code === 'mercadopago')
                                    <div id="mercadopago-info" class="alert alert-light border mt-2 small">
                                        {{ __('store.checkout.mercadopago_info') }}
                                    </div>
                                @endif

                                @if($gateway->code === 'paypal')
                                    <div id="paypal-button-container" class="mt-3" style="display: none;"></div>
                                @endif

                                @if($gateway->code === 'stripe')
                                    <div id="card-element" class="mt-3" style="display: none;"></div>
                                    <div id="card-errors" class="text-danger mt-2"></div>
                                @endif
                            @endforeach

                            <div id="payment-fields">
                                <!-- Stripe/PayPal fields injected with JS -->
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-4">
                            <button type="submit" id="place-order" class="btn btn-primary w-100">{{ __('store.checkout.place_order') }}</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-5 mt-5 mt-md-0">
                    <div class="cart-box">
                        <h3 class="cart-heading">{{ __('store.checkout.order_summary') }}</h3>

                        <div class="row border-bottom pb-2 mb-2 mt-4">
                            <div class="col-6 col-md-4">{{ __('store.checkout.subtotal') }}</div>
                            <div class="col-6 col-md-8 text-end">{{ $currency->symbol }}{{ number_format($subtotal, 2) }}</div>
                        </div>
                        <div class="row border-bottom pb-2 mb-2">
                            <div class="col-4 col-md-4">{{ __('store.checkout.shipping') }}</div>
                            <div class="col-8 col-md-8 text-end"><small>{{ __('store.checkout.shipping_info') }}</small></div>
                        </div>
                        <div class="row border-bottom pb-2 mb-2">   
                            <div class="col-6 col-md-4">{{ __('store.checkout.total') }}</div>
                            <div class="col-6 col-md-8 text-end"><span>{{ $currency->symbol }}{{ number_format($total, 2) }}</span></div>
                        </div>

                        <div class="mt-4">
                            <a href="#" class="read-more d-block text-center">{{ __('store.checkout.proceed') }}</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>

@endsection

@section('js')
@if($paypalClientId && $paypalClientId !== 'your-paypal-client-id')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ storeCurrencyCode() }}"></script>
@endif
@if($stripePublicKey && $stripePublicKey !== 'your-stripe-public-key')
<script src="https://js.stripe.com/v3/"></script>
@endif
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("checkout-form");
    const gatewayRadios = document.querySelectorAll('input[name="gateway"]');
    const paypalContainer = document.getElementById("paypal-button-container");
    const stripeContainer = document.getElementById("card-element");
    const mpInfo = document.getElementById("mercadopago-info");
    const successUrl = @json(route('checkout.success'));

    let stripe = null;
    let card = null;
    @if($stripePublicKey && $stripePublicKey !== 'your-stripe-public-key')
    stripe = Stripe(@json($stripePublicKey));
    const elements = stripe.elements();
    card = elements.create("card");
    if (stripeContainer) {
        card.mount("#card-element");
    }
    @endif

    function togglePaymentUi(gateway) {
        if (paypalContainer) {
            paypalContainer.style.display = gateway === "paypal" ? "block" : "none";
        }
        if (stripeContainer) {
            stripeContainer.style.display = gateway === "stripe" ? "block" : "none";
        }
        if (mpInfo) {
            mpInfo.style.display = gateway === "mercadopago" ? "block" : "none";
        }
    }

    gatewayRadios.forEach(radio => {
        radio.addEventListener("change", function () {
            togglePaymentUi(this.value);
        });
        if (radio.checked) {
            togglePaymentUi(radio.value);
        }
    });

    @if($paypalClientId && $paypalClientId !== 'your-paypal-client-id')
    if (typeof paypal !== "undefined" && paypalContainer) {
        paypal.Buttons({
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{ amount: { value: "{{ number_format($total, 2, '.', '') }}" } }]
                });
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function () {
                    window.location.href = @json(route('paypal.success')) + "?token=" + data.orderID;
                });
            }
        }).render("#paypal-button-container");
    }
    @endif

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const selected = document.querySelector('input[name="gateway"]:checked');
        if (!selected) {
            showStoreToast('error', @json(__('store.checkout.payment_required')));
            return;
        }

        const gateway = selected.value;
        const formData = new FormData(form);

        if (gateway === "paypal") {
            showStoreToast('info', @json(__('store.checkout.paypal_instructions')));
            return;
        }

        if (gateway === "stripe") {
            if (!stripe || !card) {
                showStoreToast('error', "Stripe no está configurado.");
                return;
            }
            const {paymentMethod, error} = await stripe.createPaymentMethod({ type: "card", card });
            if (error) {
                document.getElementById("card-errors").textContent = error.message;
                return;
            }
            formData.append("payment_method_id", paymentMethod.id);
        }

        const response = await fetch(@json(route('checkout.process')), {
            method: "POST",
            headers: { "X-CSRF-TOKEN": @json(csrf_token()), "Accept": "application/json" },
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            showStoreToast('error', result.message || @json(__('store.checkout.order_failed')));
            return;
        }

        if (result.redirect) {
            window.location.href = result.redirect;
            return;
        }

        window.location.href = successUrl;
    });
});
</script>
@endsection