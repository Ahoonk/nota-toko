<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\ItemCategory;
use App\Models\Unit;

return [
    'roles' => [
        'admin',
        'staff',
        'viewer',
    ],

    'document_types' => [
        'nota' => 'Nota',
        'faktur' => 'Faktur',
        'kuitansi' => 'Kuitansi',
    ],

    'master_resources' => [
        'companies' => [
            'model' => Company::class,
            'title' => 'Perusahaan',
            'search' => ['name', 'email', 'phone', 'website'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nama Perusahaan', 'type' => 'text', 'required' => true],
                ['name' => 'logo', 'label' => 'Logo', 'type' => 'file'],
                ['name' => 'address', 'label' => 'Alamat', 'type' => 'textarea'],
                ['name' => 'phone', 'label' => 'Telepon', 'type' => 'text'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                ['name' => 'website', 'label' => 'Website', 'type' => 'text'],
                ['name' => 'npwp', 'label' => 'NPWP', 'type' => 'text'],
                ['name' => 'responsible_name', 'label' => 'Penanggung Jawab', 'type' => 'text'],
                ['name' => 'responsible_position', 'label' => 'Jabatan', 'type' => 'text'],
                ['name' => 'signature_path', 'label' => 'Upload Tanda Tangan', 'type' => 'file'],
            ],
            'columns' => ['name', 'phone', 'email', 'website'],
            'defaults' => [
                'phone' => null,
                'email' => null,
                'website' => null,
                'npwp' => null,
                'responsible_name' => null,
                'responsible_position' => null,
            ],
        ],
        'customers' => [
            'model' => Customer::class,
            'title' => 'Pelanggan',
            'search' => ['name', 'company_name', 'phone', 'email'],
            'fields' => [
                ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select', 'required' => true],
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'required' => true],
                ['name' => 'company_name', 'label' => 'Perusahaan Pelanggan', 'type' => 'text'],
                ['name' => 'address', 'label' => 'Alamat', 'type' => 'textarea'],
                ['name' => 'phone', 'label' => 'Nomor HP', 'type' => 'text'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ],
            'columns' => ['name', 'company_name', 'phone', 'email'],
            'defaults' => [],
        ],
        'item-categories' => [
            'model' => ItemCategory::class,
            'title' => 'Jenis Barang',
            'search' => ['name'],
            'fields' => [
                ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select', 'required' => true],
                ['name' => 'name', 'label' => 'Jenis Barang', 'type' => 'text', 'required' => true],
                ['name' => 'description', 'label' => 'Keterangan', 'type' => 'textarea'],
            ],
            'columns' => ['name', 'description'],
            'defaults' => [],
        ],
        'units' => [
            'model' => Unit::class,
            'title' => 'Satuan',
            'search' => ['name', 'symbol'],
            'fields' => [
                ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select', 'required' => true],
                ['name' => 'name', 'label' => 'Satuan', 'type' => 'text', 'required' => true],
                ['name' => 'symbol', 'label' => 'Simbol', 'type' => 'text'],
            ],
            'columns' => ['name', 'symbol'],
            'defaults' => [],
        ],
        'items' => [
            'model' => \App\Models\Item::class,
            'title' => 'Barang',
            'search' => ['name', 'brand'],
            'fields' => [
                ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select', 'required' => true],
                ['name' => 'item_category_id', 'label' => 'Jenis Barang', 'type' => 'select', 'required' => true],
                ['name' => 'unit_id', 'label' => 'Satuan', 'type' => 'select', 'required' => true],
                ['name' => 'name', 'label' => 'Nama Barang', 'type' => 'text', 'required' => true],
                ['name' => 'brand', 'label' => 'Merek', 'type' => 'text'],
                ['name' => 'default_price', 'label' => 'Harga Default', 'type' => 'number', 'step' => '0.01', 'required' => true],
            ],
            'columns' => ['name', 'brand', 'default_price'],
            'defaults' => [
                'default_price' => 0,
            ],
        ],
    ],
];
