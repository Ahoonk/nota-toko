@extends('layouts.app')

@section('title', 'Detail Transaksi - Nota Toko')

@php
    $totalProfit = $transaction->details->sum(fn ($detail) => $detail->profit_amount);
@endphp

@push('styles')
<style>
    .detail-hero {
        position: relative;
        overflow: hidden;
    }

    .detail-hero::after {
        content: '';
        position: absolute;
        inset: auto -10% -40% auto;
        width: 20rem;
        height: 20rem;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,.18) 0%, rgba(255,255,255,0) 68%);
        pointer-events: none;
    }

    .hero-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .8rem;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        border: 1px solid rgba(255,255,255,.18);
    }

    .detail-stat {
        height: 100%;
        padding: 1rem 1.1rem;
        border-radius: 1rem;
        background: rgba(255,255,255,.9);
        border: 1px solid rgba(15, 23, 42, .06);
        box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
    }

    .detail-stat .label {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
        margin-bottom: .35rem;
    }

    .detail-stat .value {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.25;
        color: #0f172a;
    }

    .detail-panel {
        border-radius: 1.1rem;
        background: linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.84));
        border: 1px solid rgba(226,232,240,.9);
        box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
    }

    .detail-panel .section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 1rem;
    }

    .info-card {
        grid-column: span 3;
        padding: .95rem 1rem;
        border-radius: .9rem;
        background: #fff;
        border: 1px solid #e2e8f0;
    }

    .info-card .mini {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
        margin-bottom: .25rem;
    }

    .info-card .big {
        font-weight: 700;
        color: #0f172a;
    }

    .info-card.full {
        grid-column: span 12;
    }

    .brought-list {
        display: grid;
        gap: .75rem;
    }

    .brought-item {
        display: flex;
        gap: .75rem;
        align-items: flex-start;
        padding: .85rem 1rem;
        border-radius: .9rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .brought-dot {
        width: .8rem;
        height: .8rem;
        border-radius: 999px;
        margin-top: .35rem;
        background: linear-gradient(135deg, #1d4ed8, #0f766e);
        flex: 0 0 auto;
    }

    .detail-table {
        border-radius: 1rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .detail-table table {
        margin-bottom: 0;
    }

    .doc-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
    }

    .icon-action {
        width: 2.8rem;
        height: 2.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .85rem;
        border: 1px solid transparent;
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .icon-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, .12);
    }

    @media (max-width: 1199.98px) {
        .info-card {
            grid-column: span 6;
        }
    }

    @media (max-width: 575.98px) {
        .info-card {
            grid-column: span 12;
        }
    }
</style>
@endpush

@section('content')
    <x-page-hero
        eyebrow="Kasir / Detail"
        title="{{ $transaction->transaction_number }}"
        subtitle="{{ $transaction->customer_name }} / {{ $transaction->company_name }}"
        :back-url="route('transactions.index')"
        class="detail-hero"
    >
        <x-slot:actions>
            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-light btn-lg">
                <i class="bi bi-pencil-square me-1"></i>Edit Transaksi
            </a>
        </x-slot:actions>

        <x-slot:stats>
            <div class="col-md-3">
                <div class="hero-card p-3 p-lg-4">
                    <div class="label">Tanggal</div>
                    <div class="value">{{ $transaction->transaction_date?->format('d-m-Y') }}</div>
                    <div class="small text-white-50 mt-1">Transaksi yang tercatat di sistem</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="hero-card p-3 p-lg-4">
                    <div class="label">Subtotal</div>
                    <div class="value">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</div>
                    <div class="small text-white-50 mt-1">Nilai sebelum diskon transaksi</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="hero-card p-3 p-lg-4">
                    <div class="label">Grand Total</div>
                    <div class="value">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</div>
                    <div class="small text-white-50 mt-1">Total akhir yang dibayar</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="hero-card p-3 p-lg-4">
                    <div class="label">Keuntungan</div>
                    <div class="value">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
                    <div class="small text-white-50 mt-1">Hasil dari semua detail barang</div>
                </div>
            </div>
        </x-slot:stats>
    </x-page-hero>

    <div class="detail-panel p-4 mb-4">
        <div class="section-head">
            <div>
                <div class="h5 mb-1">Informasi Transaksi</div>
                <div class="text-muted small">Ringkasan data kerja dan catatan transaksi.</div>
            </div>
            <span class="badge rounded-pill text-bg-primary">Data Lengkap</span>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="mini">Jenis Pekerjaan</div>
                <div class="big">{{ $transaction->work_type ?? '-' }}</div>
            </div>
            <div class="info-card">
                <div class="mini">Tempat Pengerjaan</div>
                <div class="big">{{ $transaction->work_location ?? '-' }}</div>
            </div>
            <div class="info-card">
                <div class="mini">Lama Pengerjaan</div>
                <div class="big">{{ $transaction->work_duration ?? '-' }}</div>
            </div>
            <div class="info-card">
                <div class="mini">Customer</div>
                <div class="big">{{ $transaction->customer_name }}</div>
            </div>
            <div class="info-card full">
                <div class="mini">Barang Dibawa</div>
                @if ($transaction->broughtItems->isEmpty())
                    <div class="big">-</div>
                @else
                    <div class="brought-list">
                        @foreach ($transaction->broughtItems as $broughtItem)
                            <div class="brought-item">
                                <span class="brought-dot"></span>
                                <div>
                                    <div class="fw-semibold">{{ $broughtItem->item_name ?? $broughtItem->item?->name ?? '-' }}</div>
                                    @if ($broughtItem->notes)
                                        <div class="text-muted small">{{ $broughtItem->notes }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="info-card full">
                <div class="mini">Terbilang</div>
                <div class="big">{{ $transaction->words }}</div>
            </div>
            <div class="info-card full">
                <div class="mini">Catatan</div>
                <div class="big">{{ $transaction->notes ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="detail-panel p-4 mb-4">
        <div class="section-head">
            <div>
                <div class="h5 mb-1">Detail Barang</div>
                <div class="text-muted small">Komponen transaksi yang dihitung ke total akhir.</div>
            </div>
            <span class="badge rounded-pill text-bg-light text-primary">Items</span>
        </div>

        <div class="detail-table table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Modal</th>
                        <th>Diskon</th>
                        <th>Total</th>
                        <th>Keuntungan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->details as $detail)
                        <tr>
                            <td class="fw-semibold">{{ $detail->item_name }}</td>
                            <td>{{ $detail->qty }}</td>
                            <td>{{ $detail->unit_name }}</td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->modal, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->discount, 0, ',', '.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                            <td class="fw-semibold text-success">Rp {{ number_format($detail->profit_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="detail-panel p-4">
        <div class="section-head mb-3">
            <div>
                <div class="h5 mb-1">Cetak Dokumen</div>
                <div class="text-muted small">Akses cepat untuk preview dan cetak dokumen transaksi.</div>
            </div>
            <form method="POST" action="{{ route('transactions.paid', $transaction) }}">
                @csrf
                <button type="submit" class="btn {{ $transaction->status === 'sudah dibayar' ? 'btn-success' : 'btn-outline-success' }} btn-sm" @disabled($transaction->status === 'sudah dibayar')>
                    <i class="bi bi-check2-circle me-1"></i>{{ $transaction->status === 'sudah dibayar' ? 'Sudah Dibayar' : 'Tandai Dibayar' }}
                </button>
            </form>
        </div>

        <div class="doc-actions">
            @foreach ($documentTypes as $type => $label)
                <a href="{{ route('transactions.preview', [$transaction, $type]) }}" class="btn btn-outline-primary icon-action" target="_blank" title="Preview {{ $label }}" aria-label="Preview {{ $label }}">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('transactions.print', [$transaction, $type]) }}" class="btn btn-primary icon-action" target="_blank" title="Cetak {{ $label }}" aria-label="Cetak {{ $label }}">
                    <i class="bi bi-printer"></i>
                </a>
            @endforeach
        </div>
    </div>
@endsection
