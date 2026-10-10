<form class="store-search-form" action="{{ url('/search') }}" method="GET">
    <input type="text" id="search-input" name="q" placeholder="{{ __('store.header.search_placeholder') }}" autocomplete="off">
    <button type="submit" aria-label="{{ __('store.header.search_placeholder') }}"><i class="fa fa-search"></i></button>
    <div id="search-suggestions" class="dropdown-menu w-100 d-none"></div>
</form>
