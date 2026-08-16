<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Nota Toko'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(13,110,253,.14), transparent 28%),
                radial-gradient(circle at top right, rgba(25,135,84,.10), transparent 24%),
                #f6f8fc;
        }

        .app-shell {
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, #10203a 0%, #172b4d 100%);
            color: #fff;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            border-radius: .75rem;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,.12);
        }

        .sidebar-brand-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            background: rgba(255,255,255,.96);
            border-radius: 1rem;
            padding: .4rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .18);
        }

        .glass-card {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.5);
            box-shadow: 0 18px 45px rgba(16,24,40,.08);
            border-radius: 1rem;
        }

        .table thead th {
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .page-hero {
            background: linear-gradient(135deg, #0f1b3d 0%, #2453e6 48%, #0f7f78 100%);
            color: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 22px 55px rgba(15, 23, 42, .16);
            overflow: hidden;
        }

        .page-hero .badge {
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .page-hero .hero-meta {
            color: rgba(255,255,255,.78);
        }

        .page-hero .hero-card {
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 1rem;
            min-height: 100%;
        }

        .page-hero .hero-card .label {
            font-size: .78rem;
            color: rgba(255,255,255,.66);
            margin-bottom: .35rem;
        }

        .page-hero .hero-card .value {
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .page-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell d-flex flex-column flex-lg-row">
    <aside class="sidebar p-3 p-lg-4 flex-shrink-0" style="width: 280px;">
        <div class="d-flex align-items-center gap-3 mb-4">
            <img
                src="{{ asset('storage/logos/logos.png') }}"
                alt="Transaksi Toko"
                class="sidebar-brand-logo"
            >
            <div>
                <div class="fw-semibold fs-5">Transaksi Toko</div>
                <small class="text-white-50">Aldera Tech</small>
            </div>
        </div>

        <nav class="nav flex-column gap-2">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            @if (auth()->user()?->isAdmin())
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-person-gear me-2"></i>User</a>
            @endif
            <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}"><i class="bi bi-building me-2"></i>Perusahaan</a>
            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}"><i class="bi bi-people me-2"></i>Pelanggan</a>
            <a class="nav-link {{ request()->routeIs('item-categories.*') ? 'active' : '' }}" href="{{ route('item-categories.index') }}"><i class="bi bi-tags me-2"></i>Jenis Barang</a>
            <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}"><i class="bi bi-rulers me-2"></i>Satuan</a>
            <a class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}" href="{{ route('items.index') }}"><i class="bi bi-box-seam me-2"></i>Barang</a>
            <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}"><i class="bi bi-receipt me-2"></i>Transaksi</a>
            <a class="nav-link {{ request()->routeIs('document-templates.*') ? 'active' : '' }}" href="{{ route('document-templates.index') }}"><i class="bi bi-filetype-pdf me-2"></i>Template Dokumen</a>
        </nav>

        <hr class="border-light opacity-25 my-4">

        <div class="small text-white-50">
            Masuk sebagai<br>
            <span class="text-white fw-semibold">{{ auth()->user()->name ?? 'Guest' }}</span>
            <div class="text-uppercase mt-1">{{ auth()->user()->role ?? '-' }}</div>
        </div>

        @auth
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="btn btn-outline-light btn-sm w-100">Logout</button>
        </form>
        @endauth
    </aside>

    <main class="flex-grow-1 p-3 p-lg-4">
        @if (session('status'))
            <div class="alert alert-success glass-card">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger glass-card">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
