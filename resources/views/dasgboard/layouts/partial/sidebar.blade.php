<aside id="adminSidebar" class="sidebar">
    <div class="brand">@if($siteBrandLogo)<img class="admin-brand-logo" src="{{ $siteBrandLogo }}" alt="{{ $siteBrandName }}">@else<span class="brand-icon"><i class="fa-solid fa-store"></i></span>@endif<span>{{ $siteBrandName }}</span></div>
    <div class="nav-label">Management</div>
    <nav>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('dashboard'))
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></a>
        @endif
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('products') || auth()->user()->hasPermission('site_settings'))
        <details class="nav-group" {{ request()->routeIs('admin.products.*') ? 'open' : '' }}>
            <summary class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><i class="fa-solid fa-box"></i><span>Products (পণ্যসমূহ)</span><i class="fa-solid fa-chevron-down submenu-arrow"></i></summary>
            <div class="submenu">
                <a class="submenu-link {{ request()->routeIs('admin.products.index') || request()->routeIs('admin.products.edit') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="fa-solid fa-list-check"></i> All Product</a>
                <a class="submenu-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}" href="{{ route('admin.products.create') }}"><i class="fa-solid fa-plus"></i> Create Product</a>
            </div>
        </details>
        @endif
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('products') || auth()->user()->hasPermission('site_settings'))
        <details class="nav-group" {{ request()->routeIs('admin.modal-products.*') ? 'open' : '' }}>
            <summary class="nav-link {{ request()->routeIs('admin.modal-products.*') ? 'active' : '' }}"><i class="fa-solid fa-window-restore"></i><span>Modal</span><i class="fa-solid fa-chevron-down submenu-arrow"></i></summary>
            <div class="submenu">
                <a class="submenu-link {{ request()->routeIs('admin.modal-products.create') ? 'active' : '' }}" href="{{ route('admin.modal-products.create') }}"><i class="fa-solid fa-plus"></i> Create Modal</a>
                <a class="submenu-link {{ request()->routeIs('admin.modal-products.index', 'admin.modal-products.edit') ? 'active' : '' }}" href="{{ route('admin.modal-products.index') }}"><i class="fa-solid fa-list-check"></i> All Product</a>
            </div>
        </details>
        @endif
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('orders'))
        <details class="nav-group" {{ request()->routeIs('admin.orders.*') ? 'open' : '' }}>
            <summary class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span><i class="fa-solid fa-chevron-down submenu-arrow"></i></summary>
            <div class="submenu">
                <a class="submenu-link {{ request()->route('filter', 'all') === 'all' && request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index', 'all') }}">All Orders</a>
                <a class="submenu-link {{ request()->route('filter') === 'today' ? 'active' : '' }}" href="{{ route('admin.orders.index', 'today') }}">Today's Orders</a>
                <a class="submenu-link {{ request()->route('filter') === 'shipping' ? 'active' : '' }}" href="{{ route('admin.orders.index', 'shipping') }}">Shipping</a>
                <a class="submenu-link {{ request()->route('filter') === 'delivered' ? 'active' : '' }}" href="{{ route('admin.orders.index', 'delivered') }}">Delivered</a>
                <a class="submenu-link {{ request()->route('filter') === 'cancelled' ? 'active' : '' }}" href="{{ route('admin.orders.index', 'cancelled') }}">Cancel</a>
                <a class="submenu-link {{ request()->route('filter') === 'refunded' ? 'active' : '' }}" href="{{ route('admin.orders.index', 'refunded') }}">Refund</a>
            </div>
        </details>
        @endif
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('fake_orders'))
        <details class="nav-group" {{ request()->routeIs('admin.fake-orders.*') ? 'open' : '' }}>
            <summary class="nav-link {{ request()->routeIs('admin.fake-orders.*') ? 'active' : '' }}"><i class="fa-solid fa-user-slash"></i><span>Fake Orders</span><i class="fa-solid fa-chevron-down submenu-arrow"></i></summary>
            <div class="submenu">
                <a class="submenu-link {{ request()->routeIs('admin.fake-orders.*') && request()->route('filter', 'all') === 'all' ? 'active' : '' }}" href="{{ route('admin.fake-orders.index', 'all') }}">All Fake Orders</a>
                <a class="submenu-link {{ request()->routeIs('admin.fake-orders.*') && request()->route('filter') === 'today' ? 'active' : '' }}" href="{{ route('admin.fake-orders.index', 'today') }}">Today's Fake Orders</a>
            </div>
        </details>
        @endif
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('incomplete_orders'))
        <details class="nav-group" {{ request()->routeIs('admin.incomplete-orders.*') ? 'open' : '' }}>
            <summary class="nav-link {{ request()->routeIs('admin.incomplete-orders.*') ? 'active' : '' }}"><i class="fa-solid fa-clipboard-list"></i><span>Incomplete Orders</span><i class="fa-solid fa-chevron-down submenu-arrow"></i></summary>
            <div class="submenu">
                <a class="submenu-link {{ request()->routeIs('admin.incomplete-orders.*') && request()->route('filter', 'all') === 'all' ? 'active' : '' }}" href="{{ route('admin.incomplete-orders.index', 'all') }}">All Incomplete Orders</a>
                <a class="submenu-link {{ request()->routeIs('admin.incomplete-orders.*') && request()->route('filter') === 'today' ? 'active' : '' }}" href="{{ route('admin.incomplete-orders.index', 'today') }}">Today's Incomplete Orders</a>
            </div>
        </details>
        @endif
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('site_settings'))
        <details class="nav-group" {{ request()->routeIs('admin.landing.*', 'admin.products.*') ? 'open' : '' }}>
            <summary class="nav-link {{ request()->routeIs('admin.landing.*', 'admin.products.*') ? 'active' : '' }}"><i class="fa-solid fa-sliders"></i><span>Site Settings</span><i class="fa-solid fa-chevron-down submenu-arrow"></i></summary>
            <div class="submenu">
                <a class="submenu-link {{ request()->routeIs('admin.landing.index') ? 'active' : '' }}" href="{{ route('admin.landing.index') }}"><i class="fa-solid fa-pen-to-square"></i> All Site Content & Sections</a>
                <a class="submenu-link {{ request()->routeIs('admin.products.index', 'admin.products.edit') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="fa-solid fa-box"></i> আপনার পণ্যসমূহ</a>
                <a class="submenu-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}" href="{{ route('admin.products.create') }}"><i class="fa-solid fa-plus"></i> নতুন পণ্য যোগ করুন</a>
                @foreach(\App\Models\LandingSection::definitions() as $slug => $item)
                    <a class="submenu-link {{ request()->route('section') === $slug ? 'active' : '' }}" href="{{ route('admin.landing.edit', $slug) }}">{{ $item['label'] }}</a>
                @endforeach
            </div>
        </details>
        @endif
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('site_tracking'))
        <details class="nav-group" {{ request()->routeIs('admin.tracking.*') ? 'open' : '' }}>
            <summary class="nav-link {{ request()->routeIs('admin.tracking.*') ? 'active' : '' }}"><i class="fa-solid fa-chart-line"></i><span>Site Tracking</span><i class="fa-solid fa-chevron-down submenu-arrow"></i></summary>
            <div class="submenu">
                <a class="submenu-link {{ request()->route('provider', 'visitors') === 'visitors' && request()->routeIs('admin.tracking.*') ? 'active' : '' }}" href="{{ route('admin.tracking.edit', 'visitors') }}"><i class="fa-solid fa-users-viewfinder"></i> Visitor Tracking</a>
                <a class="submenu-link {{ request()->route('provider') === 'meta' ? 'active' : '' }}" href="{{ route('admin.tracking.edit', 'meta') }}"><i class="fa-brands fa-meta"></i> Meta Pixel</a>
                <a class="submenu-link {{ request()->route('provider') === 'google' ? 'active' : '' }}" href="{{ route('admin.tracking.edit', 'google') }}"><i class="fa-brands fa-google"></i> Google Tracking</a>
                <a class="submenu-link {{ request()->route('provider') === 'tiktok' ? 'active' : '' }}" href="{{ route('admin.tracking.edit', 'tiktok') }}"><i class="fa-brands fa-tiktok"></i> TikTok Pixel</a>
                <a class="submenu-link {{ request()->route('provider') === 'other' ? 'active' : '' }}" href="{{ route('admin.tracking.edit', 'other') }}"><i class="fa-solid fa-code"></i> Other Tracking</a>
            </div>
        </details>
        @endif
        @if(auth()->user()->isSuperAdmin())
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="fa-solid fa-user-shield"></i><span>Admin Settings</span></a>
        @endif
    </nav>
    <div class="nav-label">Account</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="nav-link logout" type="submit"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></button>
    </form>
</aside>
