@extends('layouts.app')

@section('title', 'Dashboard - Nota Toko')

@section('content')
    <x-page-hero
        eyebrow="Dashboard"
        title="Ringkasan Operasional"
        subtitle="Pantau transaksi, master data, dan dokumen terakhir dari satu layar."
    >
        <x-slot:actions>
            <a href="{{ route('transactions.create') }}" class="btn btn-light btn-lg">
                <i class="bi bi-plus-circle me-1"></i>Transaksi Baru
            </a>
            <a href="{{ route('document-templates.index') }}" class="btn btn-outline-light btn-lg">
                Template Dokumen
            </a>
        </x-slot:actions>

        <x-slot:stats>
            <div class="col-md-6 col-xl-2">
                <div class="hero-card p-3 h-100">
                    <div class="label">Jumlah Transaksi</div>
                    <div class="value">{{ $stats['transactions'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="hero-card p-3 h-100">
                    <div class="label">Jumlah Pelanggan</div>
                    <div class="value">{{ $stats['customers'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="hero-card p-3 h-100">
                    <div class="label">Jumlah Barang</div>
                    <div class="value">{{ $stats['items'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="hero-card p-3 h-100">
                    <div class="label">Jumlah Perusahaan</div>
                    <div class="value">{{ $stats['companies'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="hero-card p-3 h-100">
                    <div class="label">Dokumen Terakhir</div>
                    <div class="value">{{ $stats['documents'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="hero-card p-3 h-100">
                    <div class="label">Template Aktif</div>
                    <div class="value">{{ $templateCount }}</div>
                </div>
            </div>
        </x-slot:stats>
    </x-page-hero>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="glass-card p-4">
                <h2 class="h5 mb-3">Transaksi Terakhir</h2>
                @if ($latestTransaction)
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tr><th class="text-muted">Nomor</th><td>{{ $latestTransaction->transaction_number }}</td></tr>
                            <tr><th class="text-muted">Tanggal</th><td>{{ $latestTransaction->transaction_date?->format('d-m-Y') }}</td></tr>
                            <tr><th class="text-muted">Pelanggan</th><td>{{ $latestTransaction->customer_name }}</td></tr>
                            <tr><th class="text-muted">Total</th><td>Rp {{ number_format($latestTransaction->grand_total, 0, ',', '.') }}</td></tr>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada transaksi.</p>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="glass-card p-4 mb-3">
                <h2 class="h5 mb-3">Dokumen Terakhir</h2>
                @if ($latestDocument)
                    <div class="small text-muted">Jenis</div>
                    <div class="fw-semibold mb-2">{{ ucfirst($latestDocument->document_type) }}</div>
                    <div class="small text-muted">Transaksi</div>
                    <div class="fw-semibold mb-2">{{ $latestDocument->transaction?->transaction_number }}</div>
                    <div class="small text-muted">Dicetak oleh</div>
                    <div class="fw-semibold">{{ $latestDocument->user?->name ?? '-' }}</div>
                @else
                    <p class="text-muted mb-0">Belum ada dokumen yang dicetak.</p>
                @endif
            </div>

            <div class="glass-card p-4">
                <h2 class="h5 mb-3">Audit Terakhir</h2>
                @if ($latestAudit)
                    <div class="small text-muted">{{ $latestAudit->event }}</div>
                    <div class="fw-semibold">{{ class_basename($latestAudit->auditable_type) }}</div>
                    <div class="text-muted small mt-2">{{ $latestAudit->created_at?->format('d-m-Y H:i') }}</div>
                @else
                    <p class="text-muted mb-0">Belum ada audit log.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
