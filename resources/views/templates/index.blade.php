@extends('layouts.app')

@section('title', 'Template Dokumen - Nota Toko')

@section('content')
    <x-page-hero
        eyebrow="Template Dokumen"
        title="Kelola Template PDF"
        subtitle="Upload template PDF untuk Nota, Faktur, dan Kuitansi."
    >
        <x-slot:actions>
            <a href="{{ route('document-templates.create') }}" class="btn btn-light btn-lg">
                <i class="bi bi-upload me-1"></i>Upload Template
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Perusahaan</th>
                        <th>Jenis</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th style="width: 240px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templates as $template)
                        <tr>
                            <td>{{ $template->company?->name }}</td>
                            <td>{{ $documentTypes[$template->document_type] ?? $template->document_type }}</td>
                            <td>{{ $template->name }}</td>
                            <td>{!! $template->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' !!}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('document-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('document-templates.destroy', $template) }}" onsubmit="return confirm('Hapus template ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada template.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $templates->links() }}
    </div>
@endsection
