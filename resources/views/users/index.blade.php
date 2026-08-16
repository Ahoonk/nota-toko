@extends('layouts.app')

@section('title', 'User - Nota Toko')

@section('content')
    <x-page-hero
        eyebrow="Pengaturan"
        title="User"
        subtitle="Kelola akun pengguna yang bisa masuk ke aplikasi."
    >
        <x-slot:actions>
            <a href="{{ route('users.create') }}" class="btn btn-light btn-lg">
                <i class="bi bi-plus-circle me-1"></i>Tambah User
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="glass-card p-4">
        <form class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, email, role, atau perusahaan...">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100">Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Perusahaan</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="fw-semibold">
                                {{ $user->name }}
                                @if (auth()->id() === $user->id)
                                    <span class="badge text-bg-primary ms-2">Anda</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->company?->name ?? '-' }}</td>
                            <td>
                                <span class="badge text-bg-secondary text-uppercase">{{ $user->role }}</span>
                            </td>
                            <td>
                                <span class="badge text-bg-success">Aktif</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    @if (auth()->id() !== $user->id)
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-outline-danger" disabled>Hapus</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
@endsection
