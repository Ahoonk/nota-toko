@extends('layouts.app')

@php
    $isEdit = isset($transaction) && $transaction;
    $pageTitle = $isEdit ? 'Edit Transaksi' : 'Transaksi Baru';
    $formAction = $isEdit ? route('transactions.update', $transaction) : route('transactions.store');
    $initialDetails = old('details');
    $initialBroughtItems = old('brought_items');

    if ($initialDetails === null) {
        $initialDetails = $isEdit
            ? $transaction->details->map(function ($detail) {
                return [
                    'item_id' => $detail->item_id,
                    'item_name' => $detail->item_name,
                    'item_category_name' => $detail->item_category_name,
                    'brand' => $detail->brand,
                    'replacement_item_name' => $detail->replacement_item_name,
                    'qty' => $detail->qty,
                    'unit_id' => $detail->unit_id,
                    'unit_name' => $detail->unit_name,
                    'price' => $detail->price,
                    'modal' => $detail->modal,
                    'discount' => $detail->discount,
                ];
            })->values()->all()
            : [[]];
    }

    if (empty($initialDetails)) {
        $initialDetails = [[]];
    }

    if ($initialBroughtItems === null) {
        $initialBroughtItems = $isEdit
            ? $transaction->broughtItems->map(function ($row) {
                return [
                    'item_id' => $row->item_id,
                    'item_name' => $row->item_name ?? $row->item?->name,
                    'notes' => $row->notes,
                ];
            })->values()->all()
            : [[]];
    }

    if (empty($initialBroughtItems)) {
        $initialBroughtItems = [[]];
    }
@endphp

@section('title', $pageTitle.' - Nota Toko')

@push('styles')
<style>
    .sales-header {
        background: linear-gradient(135deg, #10203a 0%, #1d4ed8 55%, #0f766e 100%);
        color: #fff;
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .18);
    }

    .sales-header .badge {
        letter-spacing: .06em;
    }

    .section-title {
        font-weight: 700;
        letter-spacing: .01em;
    }

    .summary-sticky {
        position: sticky;
        top: 1rem;
        max-width: 100%;
    }

    .action-sticky {
        position: sticky;
        top: 1rem;
        max-width: 100%;
    }

    .detail-list {
        display: grid;
        gap: 1rem;
        overflow-x: hidden;
    }

    .detail-row {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .detail-row .mini-label {
        margin-bottom: .35rem;
    }

    .detail-row-header {
        padding: 1rem 1rem .75rem;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .detail-row-body {
        padding: 1rem;
    }

    .detail-total-box {
        min-height: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: .75rem;
        background: #f8fafc;
        border: 1px dashed #dbe3ee;
        border-radius: .85rem;
        padding: .85rem 1rem;
    }

    .mini-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
        margin-bottom: .35rem;
    }

    .money-box {
        background: linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,.04));
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 1rem;
        padding: 1rem;
    }

    .summary-total {
        font-size: 2rem;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .soft-rule {
        border-top: 1px dashed rgba(148,163,184,.5);
    }

    @media (max-width: 1399.98px) {
        .summary-sticky,
        .action-sticky {
            position: static;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const body = document.querySelector('[data-items-body]');
    const addButton = document.querySelector('[data-add-item]');
    const broughtBody = document.querySelector('[data-brought-body]');
    const addBroughtButton = document.querySelector('[data-add-brought]');
    const subtotalEl = document.querySelector('[data-subtotal]');
    const grandEl = document.querySelector('[data-grand-total]');
    const itemTemplate = document.getElementById('detail-row-template');
    const broughtTemplate = document.getElementById('brought-row-template');
    const initialDetails = @json($initialDetails);
    const initialBroughtItems = @json($initialBroughtItems);

    const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

    const lineTotal = (row) => {
        const qty = parseFloat(row.querySelector('[name$="[qty]"]').value || 0);
        const price = parseFloat(row.querySelector('[name$="[price]"]').value || 0);
        const modal = parseFloat(row.querySelector('[name$="[modal]"]').value || 0);
        const discount = parseFloat(row.querySelector('[name$="[discount]"]').value || 0);
        return (qty * price) - discount;
    };

    const lineProfit = (row) => {
        const qty = parseFloat(row.querySelector('[name$="[qty]"]').value || 0);
        const price = parseFloat(row.querySelector('[name$="[price]"]').value || 0);
        const modal = parseFloat(row.querySelector('[name$="[modal]"]').value || 0);
        const discount = parseFloat(row.querySelector('[name$="[discount]"]').value || 0);
        return (qty * (price - modal)) - discount;
    };

    const recalc = () => {
        let subtotal = 0;

        body.querySelectorAll('[data-detail-row]').forEach((row) => {
            const total = lineTotal(row);
            const profit = lineProfit(row);
            subtotal += total;
            row.querySelector('[data-line-total]').textContent = 'Rp ' + formatNumber(total);
            row.querySelector('[data-line-profit]').textContent = 'Rp ' + formatNumber(profit);
        });

        const discountTotal = parseFloat(document.querySelector('[data-discount-total]').value || 0);
        const taxTotal = parseFloat(document.querySelector('[data-tax-total]').value || 0);
        const grandTotal = subtotal - discountTotal + taxTotal;

        subtotalEl.textContent = 'Rp ' + formatNumber(subtotal);
        grandEl.textContent = 'Rp ' + formatNumber(grandTotal);
    };

    const renumberRows = () => {
        body.querySelectorAll('[data-detail-row]').forEach((row, index) => {
            row.querySelectorAll('[data-indexed-name]').forEach((input) => {
                input.name = input.dataset.indexedName.replace('__INDEX__', index);
            });
        });
    };

    const bindRow = (row) => {
        row.querySelectorAll('input, select').forEach((input) => {
            input.addEventListener('input', recalc);
            input.addEventListener('change', recalc);
        });

        row.querySelector('[data-remove-row]')?.addEventListener('click', () => {
            if (body.querySelectorAll('[data-detail-row]').length > 1) {
                row.remove();
                renumberRows();
                recalc();
            }
        });

        const itemSelect = row.querySelector('[data-item-select]');
        if (itemSelect) {
            itemSelect.addEventListener('change', () => {
                const option = itemSelect.selectedOptions[0];
                if (!option || !option.dataset.itemId) {
                    return;
                }

                row.querySelector('[name$="[item_id]"]').value = option.dataset.itemId || '';
                row.querySelector('[name$="[item_name]"]').value = option.dataset.itemName || '';
                row.querySelector('[name$="[item_category_name]"]').value = option.dataset.itemCategory || '';
                row.querySelector('[name$="[brand]"]').value = option.dataset.brand || '';
                row.querySelector('[name$="[unit_id]"]').value = option.dataset.unitId || '';
                row.querySelector('[name$="[unit_name]"]').value = option.dataset.unitName || '';
                row.querySelector('[name$="[price]"]').value = option.dataset.defaultPrice || '';
                recalc();
            });
        }
    };

    const renumberBroughtRows = () => {
        broughtBody.querySelectorAll('[data-brought-row]').forEach((row, index) => {
            row.querySelectorAll('[data-indexed-name]').forEach((input) => {
                input.name = input.dataset.indexedName.replace('__INDEX__', index);
            });
        });
    };

    const bindBroughtRow = (row) => {
        row.querySelector('[data-remove-brought]')?.addEventListener('click', () => {
            if (broughtBody.querySelectorAll('[data-brought-row]').length > 1) {
                row.remove();
                renumberBroughtRows();
            }
        });

        const itemSelect = row.querySelector('[data-brought-item-select]');
        if (itemSelect) {
            itemSelect.addEventListener('change', () => {
                const option = itemSelect.selectedOptions[0];
                row.querySelector('[name$="[item_id]"]').value = option?.dataset.itemId || '';
                row.querySelector('[name$="[item_name]"]').value = option?.dataset.itemName || '';
            });
        }
    };

    const createRow = (data = {}) => {
        const node = itemTemplate.content.firstElementChild.cloneNode(true);
        const nextIndex = body.querySelectorAll('[data-detail-row]').length;

        node.querySelectorAll('[data-indexed-name]').forEach((input) => {
            input.name = input.dataset.indexedName.replace('__INDEX__', nextIndex);
        });

        const fill = (selector, value) => {
            const input = node.querySelector(selector);
            if (input && value !== undefined && value !== null) {
                input.value = value;
            }
        };

        fill('[name$="[item_id]"]', data.item_id);
        fill('[name$="[item_name]"]', data.item_name);
        fill('[name$="[item_category_name]"]', data.item_category_name);
        fill('[name$="[brand]"]', data.brand);
        fill('[name$="[replacement_item_name]"]', data.replacement_item_name);
        fill('[name$="[qty]"]', data.qty ?? 1);
        fill('[name$="[unit_id]"]', data.unit_id);
        fill('[name$="[unit_name]"]', data.unit_name);
        fill('[name$="[price]"]', data.price);
        fill('[name$="[modal]"]', data.modal ?? 0);
        fill('[name$="[discount]"]', data.discount ?? 0);

        const itemSelect = node.querySelector('[data-item-select]');
        if (data.item_id) {
            itemSelect.value = String(data.item_id);
        }

        body.appendChild(node);
        bindRow(node);
        recalc();
    };

    const createBroughtRow = (data = {}) => {
        const node = broughtTemplate.content.firstElementChild.cloneNode(true);
        const nextIndex = broughtBody.querySelectorAll('[data-brought-row]').length;

        node.querySelectorAll('[data-indexed-name]').forEach((input) => {
            input.name = input.dataset.indexedName.replace('__INDEX__', nextIndex);
        });

        const fill = (selector, value) => {
            const input = node.querySelector(selector);
            if (input && value !== undefined && value !== null) {
                input.value = value;
            }
        };

        fill('[name$="[item_id]"]', data.item_id);
        fill('[name$="[item_name]"]', data.item_name);
        fill('[name$="[notes]"]', data.notes);

        const itemSelect = node.querySelector('[data-brought-item-select]');
        if (data.item_id) {
            itemSelect.value = String(data.item_id);
        }

        broughtBody.appendChild(node);
        bindBroughtRow(node);
    };

    initialDetails.forEach((row) => createRow(row));
    initialBroughtItems.forEach((row) => createBroughtRow(row));

    addButton?.addEventListener('click', () => createRow());
    addBroughtButton?.addEventListener('click', () => createBroughtRow());
    document.querySelector('[data-discount-total]').addEventListener('input', recalc);
    document.querySelector('[data-tax-total]').addEventListener('input', recalc);

    document.querySelector('[data-clear-details]')?.addEventListener('click', () => {
        body.innerHTML = '';
        createRow();
        recalc();
    });

    document.querySelector('[data-focus-customer]')?.addEventListener('click', () => {
        document.querySelector('[name="customer_id"]')?.focus();
    });

    recalc();
});
</script>
@endpush

@section('content')
    <div class="sales-header p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-lg-center">
            <div>
                <span class="badge text-bg-light text-primary rounded-pill px-3 py-2 mb-3">KASIR / TRANSAKSI</span>
                <h1 class="display-6 fw-bold mb-2">{{ $pageTitle }}</h1>
                <p class="mb-0 text-white-75">Satu input data untuk Nota, Faktur, dan Kuitansi dengan data yang sama.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('transactions.index') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <button type="button" class="btn btn-outline-light btn-lg" data-clear-details>
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Item
                </button>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-md-4">
                <div class="money-box h-100">
                    <div class="small text-white-50">Nomor Transaksi</div>
                    <div class="fs-4 fw-bold">{{ $isEdit ? $transaction->transaction_number : 'AUTO' }}</div>
                    <div class="small text-white-50">{{ $isEdit ? 'Nomor lama tetap dipakai saat transaksi diedit' : 'Akan dibuat otomatis saat disimpan' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="money-box h-100">
                    <div class="small text-white-50">Input Cepat</div>
                    <div class="fw-semibold">Pilih pelanggan, isi item, lalu simpan.</div>
                    <div class="small text-white-50">Panel total akan menghitung otomatis.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="money-box h-100">
                    <div class="small text-white-50">Output Dokumen</div>
                    <div class="fw-semibold">Nota, Faktur, Kuitansi</div>
                    <div class="small text-white-50">Semua dari satu transaksi yang sama.</div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $formAction }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-12 col-xxl-8">
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="section-title h5 mb-1">Informasi Transaksi</div>
                            <div class="text-muted small">Header transaksi dan data pelanggan.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-focus-customer>
                            Fokus Pelanggan
                        </button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="transaction_date" value="{{ old('transaction_date', $isEdit ? $transaction->transaction_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" class="form-control @error('transaction_date') is-invalid @enderror">
                            @error('transaction_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Perusahaan</label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                <option value="">Pilih perusahaan</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}" @selected(old('company_id', $isEdit ? $transaction->company_id : $companies->first()?->id) == $company->id)>{{ $company->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Pelanggan</label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                <option value="">Pilih pelanggan</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', $isEdit ? $transaction->customer_id : '') == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="description" value="{{ old('description', $isEdit ? $transaction->description : '') }}" class="form-control" placeholder="Misalnya: pembelian tunai">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="notes" value="{{ old('notes', $isEdit ? $transaction->notes : '') }}" class="form-control" placeholder="Catatan tambahan untuk dokumen">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis Pekerjaan</label>
                            <select name="work_type" class="form-select @error('work_type') is-invalid @enderror">
                                <option value="">Pilih jenis</option>
                                @foreach (['Perbaikan', 'Pemeliharaan', 'Pembelian'] as $option)
                                    <option value="{{ $option }}" @selected(old('work_type', $isEdit ? $transaction->work_type : '') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('work_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tempat Pengerjaan</label>
                            <select name="work_location" class="form-select @error('work_location') is-invalid @enderror">
                                <option value="">Pilih tempat</option>
                                @foreach (['Kantor', 'Workshop'] as $option)
                                    <option value="{{ $option }}" @selected(old('work_location', $isEdit ? $transaction->work_location : '') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('work_location')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Lama Pengerjaan</label>
                            <select name="work_duration" class="form-select @error('work_duration') is-invalid @enderror">
                                <option value="">Pilih durasi</option>
                                @foreach (['1 Hari', '3 Hari', '5 Hari', '7 Hari'] as $option)
                                    <option value="{{ $option }}" @selected(old('work_duration', $isEdit ? $transaction->work_duration : '') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('work_duration')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <div class="section-title h5 mb-1">Barang Dibawa</div>
                                <div class="text-muted small">Tambahkan satu atau beberapa barang bawaan dari pelanggan.</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-add-brought>
                                <i class="bi bi-plus-circle me-1"></i>Tambah Barang
                            </button>
                        </div>

                        <div class="detail-list" data-brought-body></div>

                        <template id="brought-row-template">
                            <div data-brought-row class="detail-row">
                                <div class="detail-row-body">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                        <div class="flex-grow-1" style="min-width: 240px;">
                                            <div class="mini-label">Barang</div>
                                            <select class="form-select" data-brought-item-select>
                                                <option value="">Pilih barang</option>
                                                @foreach ($items as $item)
                                                    <option value="{{ $item->id }}" data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" data-indexed-name="brought_items[__INDEX__][item_id]" name="brought_items[0][item_id]">
                                            <input type="hidden" data-indexed-name="brought_items[__INDEX__][item_name]" name="brought_items[0][item_name]">
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-brought>
                                            <i class="bi bi-trash me-1"></i>Hapus
                                        </button>
                                    </div>
                                    <div>
                                        <div class="mini-label">Keterangan</div>
                                        <input type="text" data-indexed-name="brought_items[__INDEX__][notes]" name="brought_items[0][notes]" class="form-control" placeholder="Contoh: charger, kabel, dus, dll.">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <div class="section-title h5 mb-1">Detail Barang</div>
                            <div class="text-muted small">Setiap baris bisa diisi manual atau diambil dari master barang.</div>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm px-3" data-add-item>
                            <i class="bi bi-plus-circle me-1"></i>Tambah Baris
                        </button>
                    </div>

                    <div class="detail-list" data-items-body></div>

                    <template id="detail-row-template">
                        <div data-detail-row class="detail-row">
                            <div class="detail-row-header">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                    <div class="flex-grow-1" style="min-width: 240px;">
                                        <div class="mini-label">Cari barang</div>
                                        <select class="form-select" data-item-select>
                                            <option value="">Pilih barang</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-item-id="{{ $item->id }}"
                                                    data-item-name="{{ $item->name }}"
                                                    data-item-category="{{ $item->category?->name }}"
                                                    data-brand="{{ $item->brand }}"
                                                    data-unit-id="{{ $item->unit_id }}"
                                                    data-unit-name="{{ $item->unit?->name }}"
                                                    data-default-price="{{ $item->default_price }}">
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" data-indexed-name="details[__INDEX__][item_id]" name="details[0][item_id]">
                                        <input type="hidden" data-indexed-name="details[__INDEX__][item_name]" name="details[0][item_name]">
                                        <div class="form-text mb-0">Pilih dari master atau isi manual.</div>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-row>
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </div>
                            </div>

                            <div class="detail-row-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="mini-label">Jenis barang</div>
                                        <input type="text" data-indexed-name="details[__INDEX__][item_category_name]" name="details[0][item_category_name]" class="form-control" placeholder="Jenis">
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="mini-label">Merek</div>
                                        <input type="text" data-indexed-name="details[__INDEX__][brand]" name="details[0][brand]" class="form-control" placeholder="Merek">
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <div class="mini-label">Barang pengganti</div>
                                        <input type="text" data-indexed-name="details[__INDEX__][replacement_item_name]" name="details[0][replacement_item_name]" class="form-control" placeholder="Opsional">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-1">
                                        <div class="mini-label">Qty</div>
                                        <input type="number" step="0.01" min="0" data-indexed-name="details[__INDEX__][qty]" name="details[0][qty]" class="form-control" placeholder="0">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <div class="mini-label">Satuan</div>
                                        <input type="hidden" data-indexed-name="details[__INDEX__][unit_id]" name="details[0][unit_id]">
                                        <input type="text" data-indexed-name="details[__INDEX__][unit_name]" name="details[0][unit_name]" class="form-control" placeholder="PCS">
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <div class="mini-label">Harga</div>
                                        <input type="number" step="0.01" min="0" data-indexed-name="details[__INDEX__][price]" name="details[0][price]" class="form-control" placeholder="0">
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <div class="mini-label">Modal</div>
                                        <input type="number" step="0.01" min="0" data-indexed-name="details[__INDEX__][modal]" name="details[0][modal]" class="form-control" placeholder="0">
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <div class="mini-label">Diskon</div>
                                        <input type="number" step="0.01" min="0" data-indexed-name="details[__INDEX__][discount]" name="details[0][discount]" class="form-control" value="0">
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <div class="detail-total-box">
                                            <div>
                                                <div class="mini-label mb-1">Total</div>
                                                <div class="fw-bold fs-5 mb-0" data-line-total>Rp 0</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 col-xl-2">
                                        <div class="detail-total-box">
                                            <div>
                                                <div class="mini-label mb-1">Keuntungan</div>
                                                <div class="fw-bold fs-5 mb-0 text-success" data-line-profit>Rp 0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    @if ($errors->any())
                        <div class="alert alert-danger mt-3 mb-0">
                            Ada data yang belum valid. Silakan cek kembali form transaksi.
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xxl-4">
                <div class="summary-sticky">
                    <div class="glass-card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="section-title h5 mb-1">Ringkasan Kasir</div>
                                <div class="text-muted small">Perhitungan otomatis dari detail barang.</div>
                            </div>
                            <span class="badge text-bg-primary rounded-pill">Live</span>
                        </div>

                        <div class="p-3 rounded-4 bg-primary text-white mb-3">
                            <div class="small text-white-50">Grand Total</div>
                            <div class="summary-total" data-grand-total>Rp 0</div>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <div>
                                <div class="mini-label">Subtotal</div>
                                <div class="fw-semibold fs-5" data-subtotal>Rp 0</div>
                            </div>
                            <div>
                                <label class="mini-label" for="discount_total">Diskon transaksi</label>
                                <input type="number" step="0.01" min="0" name="discount_total" id="discount_total" data-discount-total value="{{ old('discount_total', $isEdit ? $transaction->discount_total : 0) }}" class="form-control">
                            </div>
                            <div>
                                <label class="mini-label" for="tax_total">Pajak</label>
                                <input type="number" step="0.01" min="0" name="tax_total" id="tax_total" data-tax-total value="{{ old('tax_total', $isEdit ? $transaction->tax_total : 0) }}" class="form-control">
                            </div>
                        </div>

                        <hr class="soft-rule my-4">

                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Nomor transaksi</span>
                            <span>{{ $isEdit ? $transaction->transaction_number : 'AUTO' }}</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Dokumen tersedia</span>
                            <span>Nota, Faktur, Kuitansi</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Waktu simpan</span>
                            <span>{{ $isEdit ? 'Update transaksi lama' : 'Generate nomor baru' }}</span>
                        </div>
                    </div>

                    <div class="glass-card p-4 action-sticky">
                        <div class="section-title h5 mb-1">Aksi</div>
                        <div class="text-muted small mb-3">Simpan transaksi setelah semua detail selesai.</div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-1"></i>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Transaksi' }}
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
