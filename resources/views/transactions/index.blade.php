@extends('layouts.app')

@section('title', 'Transaksi - Nota Toko')

@section('content')
    <x-page-hero
        eyebrow="Kasir / Transaksi"
        title="Daftar Transaksi"
        subtitle="Satu form input untuk menghasilkan Nota, Faktur, dan Kuitansi."
    >
        <x-slot:actions>
            <a href="{{ route('transactions.create') }}" class="btn btn-light btn-lg">
                <i class="bi bi-plus-circle me-1"></i>Transaksi Baru
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Perusahaan</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_number }}</td>
                            <td>{{ $transaction->transaction_date?->format('d-m-Y') }}</td>
                            <td>{{ $transaction->customer_name }}</td>
                            <td>{{ $transaction->company_name }}</td>
                            <td>
                                @if ($transaction->status === 'sudah dibayar')
                                    <span class="badge text-bg-success rounded-pill">Sudah Dibayar</span>
                                @else
                                    <span class="badge text-bg-warning rounded-pill">Belum Dibayar</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
@endsection
