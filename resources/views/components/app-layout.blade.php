<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Bitmap Inventory OS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Tom Select Global CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.css" rel="stylesheet">

    <!-- OVERRIDES: Make Tom Select match Tailwind inputs perfectly & Mobile Polish -->
    <style>
        /* Main input box */
        .ts-control {
            border-radius: 0.75rem !important; /* rounded-xl */
            border: 1px solid #e2e8f0 !important; /* border-slate-200 */
            padding: 0.5rem 0.75rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            background-color: #ffffff !important;
            min-height: 42px !important;
        }
        /* When clicked/focused */
        .ts-control.focus {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2) !important;
        }
        /* The dropdown menu itself */
        .ts-dropdown {
            font-size: 0.875rem !important;
            border-radius: 0.75rem !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            margin-top: 4px !important;
            z-index: 50 !important;
        }
        /* Highlighted item in the dropdown */
        .ts-dropdown .active {
            background-color: #eef2ff !important;
            color: #4338ca !important;
        }

        /* Prevent auto-zooming on iOS inputs while keeping desktop size clean */
        @media (max-width: 640px) {
            input, select, textarea, .ts-control {
                font-size: 16px !important;
            }
        }

        /* Smooth momentum scrolling for mobile horizontal tables */
        .overflow-x-auto, main {
            -webkit-overflow-scrolling: touch;
        }

        /* Clean subtle scrollbars for touch tables */
        .overflow-x-auto::-webkit-scrollbar {
            height: 5px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 9999px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-900 flex overflow-hidden">

    <!-- ========================================================================= -->
    <!-- MOBILE OFF-CANVAS SLIDE-OVER DRAWER & BACKDROP (< md) -->
    <!-- ========================================================================= -->
    <div id="mobile-nav-backdrop" class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs z-40 transition-opacity duration-300 opacity-0 pointer-events-none md:hidden" onclick="toggleMobileNav(false)" aria-hidden="true"></div>

    <aside id="mobile-nav-drawer" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-slate-900 border-r border-slate-800 flex flex-col transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden shadow-2xl" aria-label="Mobile Navigation Drawer">
        <!-- Drawer Header with Logo & Close Button -->
        <div class="flex h-16 shrink-0 items-center justify-between px-5 border-b border-slate-800/80">
            <a href="{{ route('dashboard') }}" class="text-white text-base font-black tracking-wider flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-black shadow-inner">B</span>
                BITMAP<span class="text-indigo-400 font-medium">.OS</span>
            </a>
            <button type="button" onclick="toggleMobileNav(false)" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer" aria-label="Close navigation menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Drawer Navigation Items -->
        <div class="flex flex-1 flex-col overflow-y-auto px-4 py-4">
            <nav class="flex-1 space-y-1">
                <p class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Core Operations</p>
                
                <a href="{{ route('dashboard') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    Command Center
                </a>

                <a href="{{ route('inventory.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('inventory.*') && !request()->routeIs('inventory.audit') && !request()->routeIs('inventory.valuation') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('inventory.*') && !request()->routeIs('inventory.audit') && !request()->routeIs('inventory.valuation') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Inventory Assets
                </a>

                <a href="{{ route('products.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('products.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    Product Catalog
                </a>

                <a href="{{ route('purchase-orders.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('purchase-orders.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('purchase-orders.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Purchase Orders
                </a>

                <a href="{{ route('stock-counts.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('stock-counts.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('stock-counts.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Cycle Counts
                </a>

                <a href="{{ route('inventory.valuation') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('inventory.valuation') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('inventory.valuation') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Valuation & COGS
                </a>

                <a href="{{ route('inventory.audit') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('inventory.audit') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('inventory.audit') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Audit & Transfers
                </a>
                
                <p class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider pt-5 pb-2">Settings & Master Data</p>

                <a href="{{ route('categories.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                    Categories
                </a>

                <a href="{{ route('brands.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('brands.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('brands.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    Brands
                </a>

                <a href="{{ route('locations.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('locations.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('locations.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    Locations & Sites
                </a>

                <a href="{{ route('clients.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('clients.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('clients.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                    Client Accounts
                </a>

                <a href="{{ route('suppliers.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('suppliers.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('suppliers.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.5a1.5 1.5 0 00-1.5-1.5H4.5A1.5 1.5 0 003 4.5v10.5m18 0h-3.75" />
                    </svg>
                    Suppliers
                </a>

                @auth
                @if(Auth::user()->isAdmin())
                <a href="{{ route('users.index') }}" onclick="toggleMobileNav(false)" class="{{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    User Accounts
                </a>
                @endif
                @endauth
            </nav>

            <!-- User Profile in Mobile Drawer -->
            @auth
            <div class="shrink-0 border-t border-slate-800/80 pt-4 mt-auto">
                <div class="flex items-center justify-between px-2.5 py-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
                            <span class="inline-block text-[10px] font-semibold text-indigo-400 truncate">
                                {{ Auth::user()->role_title }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="Sign Out" class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-700/50 transition-colors cursor-pointer" aria-label="Sign out">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </div>
    </aside>

    <!-- ========================================================================= -->
    <!-- DESKTOP SIDEBAR (>= md) -->
    <!-- ========================================================================= -->
    <div class="hidden md:flex md:w-64 md:flex-col bg-slate-900 border-r border-slate-800 shrink-0">
        <div class="flex h-16 shrink-0 items-center px-6 border-b border-slate-800/80">
            <a href="{{ route('dashboard') }}" class="text-white text-lg font-black tracking-wider flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-black shadow-inner">B</span>
                BITMAP<span class="text-indigo-400 font-medium">.OS</span>
            </a>
        </div>
        <div class="flex flex-1 flex-col overflow-y-auto px-4 py-5">
            <nav class="flex-1 space-y-1.5">
                <p class="px-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Core Operations</p>
                
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    Command Center
                </a>

                <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') && !request()->routeIs('inventory.audit') && !request()->routeIs('inventory.valuation') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('inventory.*') && !request()->routeIs('inventory.audit') && !request()->routeIs('inventory.valuation') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Inventory Assets
                </a>

                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('products.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    Product Catalog
                </a>

                <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('purchase-orders.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Purchase Orders
                </a>

                <a href="{{ route('stock-counts.index') }}" class="{{ request()->routeIs('stock-counts.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('stock-counts.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Cycle Counts
                </a>

                <a href="{{ route('inventory.valuation') }}" class="{{ request()->routeIs('inventory.valuation') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('inventory.valuation') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Valuation & COGS
                </a>

                <a href="{{ route('inventory.audit') }}" class="{{ request()->routeIs('inventory.audit') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('inventory.audit') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Audit & Transfers
                </a>
                
                <p class="px-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider pt-6 pb-2">Settings & Master Data</p>

                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                    Categories
                </a>

                <a href="{{ route('brands.index') }}" class="{{ request()->routeIs('brands.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('brands.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    Brands
                </a>

                <a href="{{ route('locations.index') }}" class="{{ request()->routeIs('locations.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('locations.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    Locations & Sites
                </a>

                <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('clients.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                    Client Accounts
                </a>

                <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('suppliers.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.5a1.5 1.5 0 00-1.5-1.5H4.5A1.5 1.5 0 003 4.5v10.5m18 0h-3.75" />
                    </svg>
                    Suppliers
                </a>

                @auth
                @if(Auth::user()->isAdmin())
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    User Accounts
                </a>
                @endif
                @endauth
            </nav>

            <!-- User Profile in Desktop Sidebar -->
            @auth
            <div class="shrink-0 border-t border-slate-800/80 pt-4 mt-auto">
                <div class="flex items-center justify-between px-2.5 py-2 rounded-xl bg-slate-800/60 border border-slate-700/60">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
                            <span class="inline-block text-[10px] font-semibold text-indigo-400 truncate">
                                {{ Auth::user()->role_title }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="Sign Out" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-700/50 transition-colors cursor-pointer" aria-label="Sign out">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MAIN CONTENT AREA -->
    <!-- ========================================================================= -->
    <div class="flex flex-1 flex-col w-0 overflow-hidden bg-slate-50">
        
        <!-- Top Header Navigation Bar (Responsive on Mobile and Desktop) -->
        <header class="relative z-10 flex h-16 shrink-0 bg-white border-b border-slate-200/80 shadow-xs">
            <div class="flex flex-1 items-center justify-between px-3 sm:px-6 lg:px-8 gap-2 sm:gap-4">
                
                <!-- Left Section: Mobile Hamburger Button & Mobile Logo -->
                <div class="flex items-center gap-2 md:hidden">
                    <button type="button" onclick="toggleMobileNav(true)" class="p-2 -ml-1 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Open mobile navigation menu">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 shrink-0" title="Bitmap Inventory OS">
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-black shadow-xs">B</span>
                        <span class="font-black text-sm tracking-tight text-slate-900 hidden xs:inline">BITMAP<span class="text-indigo-600 font-medium">.OS</span></span>
                    </a>
                </div>

                <!-- Center Section: Quick Search Bar -->
                <div class="flex flex-1 items-center max-w-xs sm:max-w-md">
                    <form method="GET" action="{{ route('inventory.index') }}" class="w-full relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input type="text" name="search" class="block w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2 text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-xs sm:text-sm bg-slate-50/50" placeholder="Search assets...">
                    </form>
                </div>

                <!-- Right Section: Badges & User Status -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-lg bg-slate-100 text-[11px] font-semibold text-slate-600">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        PostgreSQL Live
                    </div>

                    @auth
                    <div class="flex items-center gap-2 sm:gap-3 pl-2 sm:pl-3 border-l border-slate-200">
                        @php
                            $roleColors = [
                                'admin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'warehouse' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'engineer' => 'bg-sky-50 text-sky-700 border-sky-200',
                                'viewer' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            ];
                            $colorClass = $roleColors[Auth::user()->role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                        @endphp
                        <span class="hidden md:inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $colorClass }}">
                            {{ Auth::user()->role_title }}
                        </span>

                        <div class="flex items-center gap-2" title="{{ Auth::user()->name }} ({{ Auth::user()->role_title }})">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="font-bold text-xs text-slate-700 hidden lg:inline">{{ Auth::user()->name }}</span>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Sign Out" class="inline-flex items-center gap-1 p-2 sm:px-2.5 sm:py-1 text-xs font-semibold text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" aria-label="Sign out">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                                <span class="hidden sm:inline">Sign Out</span>
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>

            </div>
        </header>

        <!-- Page Content Scroll Container -->
        <main class="flex-1 overflow-y-auto focus:outline-none pb-20 md:pb-6">
            <div class="py-4 sm:py-6">
                <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <!-- ========================================================================= -->
        <!-- MOBILE BOTTOM NAVIGATION TAB BAR (Fixed on Mobile Screens < md) -->
        <!-- ========================================================================= -->
        <nav class="fixed bottom-0 inset-x-0 z-30 md:hidden bg-slate-900/95 backdrop-blur-md border-t border-slate-800 text-slate-400 py-1.5 px-2 safe-area-pb" aria-label="Mobile Quick Navigation">
            <div class="grid grid-cols-5 gap-0.5 text-center items-center">
                
                <!-- 1. Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-1 rounded-lg {{ request()->routeIs('dashboard') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <span class="text-[10px] mt-0.5">Command</span>
                </a>

                <!-- 2. Inventory Assets -->
                <a href="{{ route('inventory.index') }}" class="flex flex-col items-center justify-center py-1 rounded-lg {{ request()->routeIs('inventory.*') && !request()->routeIs('inventory.audit') && !request()->routeIs('inventory.valuation') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="text-[10px] mt-0.5">Assets</span>
                </a>

                <!-- 3. Product Catalog -->
                <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center py-1 rounded-lg {{ request()->routeIs('products.*') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    <span class="text-[10px] mt-0.5">Catalog</span>
                </a>

                <!-- 4. Cycle Counts (Shelf Audit) -->
                <a href="{{ route('stock-counts.index') }}" class="flex flex-col items-center justify-center py-1 rounded-lg {{ request()->routeIs('stock-counts.*') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[10px] mt-0.5">Audits</span>
                </a>

                <!-- 5. More Menu Trigger -->
                <button type="button" onclick="toggleMobileNav(true)" class="flex flex-col items-center justify-center py-1 rounded-lg text-slate-400 hover:text-slate-200 cursor-pointer" aria-label="Open all navigation menus">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <span class="text-[10px] mt-0.5">More</span>
                </button>

            </div>
        </nav>

    </div>

    <!-- Tom Select Global JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.searchable-select').forEach((el) => {
                new TomSelect(el, {
                    create: false, 
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    placeholder: 'Type to search...'
                });
            });
        });

        // Mobile Slide-Over Navigation Controller
        function toggleMobileNav(open) {
            const backdrop = document.getElementById('mobile-nav-backdrop');
            const drawer = document.getElementById('mobile-nav-drawer');
            if (!backdrop || !drawer) return;

            if (open) {
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                drawer.classList.remove('-translate-x-full');
                drawer.classList.add('translate-x-0');
                document.body.classList.add('overflow-hidden');
            } else {
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                drawer.classList.remove('translate-x-0');
                drawer.classList.add('-translate-x-full');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Close drawer on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                toggleMobileNav(false);
            }
        });
    </script>
</body>
</html>
