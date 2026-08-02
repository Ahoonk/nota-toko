@extends('layouts.app')

@section('title', ($record ? 'Edit ' : 'Tambah ').$definition['title'].' - Nota Toko')

@section('content')
    <x-page-hero
        eyebrow="Master Data"
        title="{{ $record ? 'Edit' : 'Tambah' }} {{ $definition['title'] }}"
        subtitle="Lengkapi form berikut untuk menyimpan data."
        :back-url="route($resource.'.index')"
    />

    <div class="glass-card p-4">
        <form method="POST" action="{{ $record ? route($resource.'.update', $record->id) : route($resource.'.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($record)
                @method('PUT')
            @endif

            <div class="row g-3">
                @foreach ($fields as $field)
                    <div class="col-md-6 {{ in_array($field['type'], ['textarea'], true) ? 'col-12' : '' }}">
                        <label class="form-label">{{ $field['label'] }} @if(!empty($field['required'])) <span class="text-danger">*</span> @endif</label>

                        @if ($field['type'] === 'textarea')
                            <textarea name="{{ $field['name'] }}" class="form-control @error($field['name']) is-invalid @enderror" rows="4">{{ old($field['name'], $record?->{$field['name']} ?? '') }}</textarea>
                        @elseif ($field['type'] === 'select')
                            <select name="{{ $field['name'] }}" class="form-select @error($field['name']) is-invalid @enderror">
                                <option value="">Pilih</option>
                                @foreach ($selectOptions[$field['name']] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(old($field['name'], $record?->{$field['name']} ?? '') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif ($field['type'] === 'file')
                            <input type="file" name="{{ $field['name'] }}" class="form-control @error($field['name']) is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            @if ($record && !empty($record->{$field['name']}))
                                <div class="small text-muted mt-2">File saat ini: <a href="{{ asset('storage/'.$record->{$field['name']}) }}" target="_blank">lihat</a></div>
                            @endif
                        @elseif ($field['type'] === 'number')
                            <input type="number" step="{{ $field['step'] ?? '1' }}" name="{{ $field['name'] }}" value="{{ old($field['name'], $record?->{$field['name']} ?? ($definition['defaults'][$field['name']] ?? '')) }}" class="form-control @error($field['name']) is-invalid @enderror">
                        @else
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ old($field['name'], $record?->{$field['name']} ?? ($definition['defaults'][$field['name']] ?? '')) }}" class="form-control @error($field['name']) is-invalid @enderror">
                        @endif

                        @error($field['name'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
