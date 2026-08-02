@extends('layouts.app')

@section('title', $definition['title'].' - Nota Toko')

@section('content')
    <x-page-hero
        eyebrow="Master Data"
        title="{{ $definition['title'] }}"
        subtitle="Kelola data {{ strtolower($definition['title']) }} di sini."
    >
        <x-slot:actions>
            <a href="{{ route($resource.'.create') }}" class="btn btn-light btn-lg">
                <i class="bi bi-plus-circle me-1"></i>Tambah
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="glass-card p-4">
        <form class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari data...">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100">Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        @foreach ($definition['columns'] as $column)
                            <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                        @endforeach
                        <th style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            @foreach ($definition['columns'] as $column)
                                <td>
                                    @if (in_array($column, ['default_price', 'subtotal', 'discount_total', 'tax_total', 'grand_total'], true))
                                        Rp {{ number_format((float) $record->{$column}, 0, ',', '.') }}
                                    @else
                                        {{ $record->{$column} }}
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route($resource.'.edit', $record->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route($resource.'.destroy', $record->id) }}" onsubmit="return confirm('Hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($definition['columns']) + 1 }}" class="text-center text-muted py-4">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $records->links() }}
    </div>
@endsection
