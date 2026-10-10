@php
    $wishlistCount = 0;
    if (auth('customer')->check()) {
        $wishlistCount = auth('customer')->user()->wishlistProducts()->count();
    }
    $cartCount = session('cart') ? collect(session('cart'))->sum('quantity') : 0;
@endphp

<header class="store-header">
    <div class="store-topbar d-none d-md-block">
        {{ __('store.header.top_bar_message') }}
    </div>
    <div class="store-topbar d-md-none">
        {{ __('store.header.top_bar_short') }}
    </div>

    <div class="store-header__bar">
        <button type="button" class="store-menu-toggle d-lg-none" id="store-menu-open" aria-label="{{ __('store.header.open_menu') }}">
            <i class="fa-solid fa-bars"></i>
        </button>

        <a href="{{ route('xylo.home') }}" class="store-logo">
            <img src="{{ asset('assets/logotipos/logo.png') }}" alt="{{ config('app.name') }}" />
        </a>

        <div class="store-header__actions">
            <button type="button" class="store-icon-btn d-lg-none" id="store-search-toggle" aria-label="{{ __('store.header.search_placeholder') }}">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

            <a href="{{ auth('customer')->check() ? route('customer.wishlist.index') : route('customer.login') }}" class="store-icon-btn d-none d-sm-inline-flex">
                <i class="fa-regular fa-heart"></i>
                @if($wishlistCount > 0)
                    <span class="badge-count" id="wishlist-count">{{ $wishlistCount }}</span>
                @endif
            </a>

            <div class="dropdown">
                <a href="#" class="store-icon-btn" data-bs-toggle="dropdown" aria-label="Cuenta">
                    @auth('customer')
                        @php $customer = Auth::guard('customer')->user(); @endphp
                        @if($customer->profile_image)
                            <img src="{{ asset('storage/' . $customer->profile_image) }}" alt="" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}" alt="" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;">
                        @endif
                    @else
                        <i class="fa-regular fa-user"></i>
                    @endauth
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2">
                    @guest('customer')
                        <li><a class="dropdown-item rounded-2" href="{{ route('customer.login') }}">{{ __('store.login.login_btn') }}</a></li>
                        <li><a class="dropdown-item rounded-2" href="{{ route('customer.register') }}">{{ __('store.register.signup_btn') }}</a></li>
                    @else
                        <li><a class="dropdown-item rounded-2" href="{{ route('customer.profile.edit') }}">{{ __('store.profile.title') }}</a></li>
                        <li>
                            <a class="dropdown-item rounded-2" href="{{ route('customer.logout') }}"
                               onclick="event.preventDefault(); document.getElementById('customer-logout-form').submit();">
                                {{ __('store.header.logout') }}
                            </a>
                            <form id="customer-logout-form" action="{{ route('customer.logout') }}" method="POST" class="d-none">@csrf</form>
                        </li>
                    @endguest
                </ul>
            </div>

            <a href="{{ route('cart.view') }}" class="store-icon-btn">
                <i class="fa-solid fa-bag-shopping"></i>
                <span class="badge-count" id="cart-count">{{ $cartCount }}</span>
            </a>
        </div>
    </div>

    <div class="store-search-wrap is-collapsed" id="store-search-wrap">
        @include('themes.xylo.partials.search-form')
    </div>

    <nav class="store-nav-desktop">
        <ul class="nav">
            @if ($headerMenu && $headerMenu->menuItems->count())
                @foreach ($headerMenu->menuItems as $menuItem)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url($menuItem->slug) }}">
                            {{ $menuItem->translation->title ?? 'No Translation' }}
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
    </nav>
</header>

<div class="store-drawer-overlay" id="store-drawer-overlay" aria-hidden="true"></div>
<aside class="store-drawer" id="store-mobile-drawer" aria-hidden="true">
    <div class="store-drawer__head">
        <strong>{{ config('app.name', 'Tienda Digital') }}</strong>
        <button type="button" class="store-drawer__close" id="store-menu-close" aria-label="{{ __('store.header.close_menu') }}">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <nav class="store-drawer__nav">
        @if ($headerMenu && $headerMenu->menuItems->count())
            @foreach ($headerMenu->menuItems as $menuItem)
                <a href="{{ url($menuItem->slug) }}">{{ $menuItem->translation->title ?? 'Menú' }}</a>
            @endforeach
        @endif
        <a href="{{ route('shop.index') }}">{{ __('store.home.shop_now') }}</a>
        <a href="{{ route('cart.view') }}">{{ __('store.cart.breadcrumb_cart') }}</a>
    </nav>
    <div class="store-drawer__foot">
        @guest('customer')
            <a href="{{ route('customer.login') }}"><i class="fa-regular fa-user me-2"></i>{{ __('store.login.login_btn') }}</a>
            <a href="{{ route('customer.register') }}">{{ __('store.register.signup_btn') }}</a>
        @else
            <a href="{{ route('customer.profile.edit') }}">{{ __('store.profile.title') }}</a>
            <a href="{{ route('customer.wishlist.index') }}">{{ __('store.wishlist.title') }}</a>
        @endguest
    </div>
</aside>
