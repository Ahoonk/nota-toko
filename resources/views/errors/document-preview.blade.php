@extends('layouts.app')

@section('title', 'Preview Dokumen - Error')

@section('content')
    <x-page-hero
        eyebrow="Preview Dokumen"
        title="Dokumen gagal diproses"
        subtitle="Template PDF belum bisa dirender untuk transaksi ini."
    />

    <div class="glass-card p-4">
        <div class="alert alert-warning mb-4">
            <div class="fw-semibold mb-1">Pesan error</div>
            <div>{{ $message }}</div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="p-3 border rounded-3 bg-white h-100">
                    <div class="fw-semibold mb-2">Info transaksi</div>
                    <div class="mb-1"><span class="text-muted">Nomor:</span> {{ $transaction->transaction_number }}</div>
                    <div class="mb-1"><span class="text-muted">Jenis dokumen:</span> {{ $documentLabel }}</div>
                    <div class="mb-1"><span class="text-muted">Preview:</span> {{ $preview ? 'Ya' : 'Tidak' }}</div>
                    <div><span class="text-muted">Perusahaan:</span> {{ $transaction->company_name }}</div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="p-3 border rounded-3 bg-white h-100">
                    <div class="fw-semibold mb-2">Langkah cepat</div>
                    <ul class="mb-0 ps-3">
                        <li>Pastikan template PDF sudah aktif untuk perusahaan yang benar.</li>
                        <li>Pastikan file PDF tidak rusak dan bisa dibuka di komputer lain.</li>
                        <li>Kalau template baru berbeda format, sesuaikan dulu layout-nya.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4 flex-wrap">
            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-primary">Kembali ke Transaksi</a>
            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Kembali</button>
        </div>
    </div>
@endsection
