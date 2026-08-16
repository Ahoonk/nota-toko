@extends('layouts.app')

@section('title', ($record ? 'Edit' : 'Tambah').' User - Nota Toko')

@section('content')
    <x-page-hero
        eyebrow="Pengaturan"
        title="{{ $record ? 'Edit' : 'Tambah' }} User"
        subtitle="Isi data akun pengguna dengan benar."
        :back-url="route('users.index')"
    />

    <div class="glass-card p-4">
        <form method="POST" action="{{ $record ? route('users.update', $record->id) : route('users.store') }}">
            @csrf
            @if ($record)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Perusahaan <span class="text-danger">*</span></label>
                    <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                        <option value="">Pilih perusahaan</option>
                        @foreach ($companies as $id => $name)
                            <option value="{{ $id }}" @selected(old('company_id', $record?->company_id ?? auth()->user()?->company_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('company_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror">
                        <option value="">Pilih role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $record?->role) === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $record?->name) }}" class="form-control @error('name') is-invalid @enderror">
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $record?->email) }}" class="form-control @error('email') is-invalid @enderror">
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password @if (! $record) <span class="text-danger">*</span> @endif</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    <div class="form-text">
                        {{ $record ? 'Kosongkan jika tidak ingin mengubah password.' : 'Minimal 6 karakter.' }}
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password @if (! $record) <span class="text-danger">*</span> @endif</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
