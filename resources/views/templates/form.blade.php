@extends('layouts.app')

@section('title', ($template ? 'Edit' : 'Upload').' Template - Nota Toko')

@section('content')
    <x-page-hero
        eyebrow="Template Dokumen"
        title="{{ $template ? 'Edit' : 'Upload' }} Template"
        subtitle="Gunakan file PDF sebagai dasar template dokumen."
        :back-url="route('document-templates.index')"
    />

    <div class="glass-card p-4">
        <form method="POST" action="{{ $template ? route('document-templates.update', $template) : route('document-templates.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($template)
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Perusahaan</label>
                    <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                        <option value="">Pilih</option>
                        @foreach ($companies as $id => $name)
                            <option value="{{ $id }}" @selected(old('company_id', $template?->company_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('company_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jenis Dokumen</label>
                    <select name="document_type" class="form-select @error('document_type') is-invalid @enderror">
                        <option value="">Pilih</option>
                        @foreach ($documentTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('document_type', $template?->document_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('document_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Template</label>
                    <input type="text" name="name" value="{{ old('name', $template?->name) }}" class="form-control @error('name') is-invalid @enderror">
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">File PDF</label>
                    <input type="file" name="template_file" class="form-control @error('template_file') is-invalid @enderror" accept=".pdf">
                    @if ($template)
                        <div class="small text-muted mt-2">File saat ini: <a href="{{ asset('storage/'.$template->template_path) }}" target="_blank">lihat</a></div>
                    @endif
                    @error('template_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4">{{ old('notes', $template?->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                @if ($template)
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $template->is_active))>
                            <label class="form-check-label" for="is_active">Aktifkan template ini</label>
                        </div>
                    </div>
                @endif
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('document-templates.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
