<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::updateOrCreate([
            'name' => 'Nota Toko Utama',
        ], [
            'address' => 'Jl. Contoh No. 1',
            'phone' => '08123456789',
            'email' => 'admin@notatoko.test',
            'website' => 'https://notatoko.test',
            'npwp' => '00.000.000.0-000.000',
            'responsible_name' => 'Administrator',
            'responsible_position' => 'Owner',
        ]);

        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'company_id' => $company->id,
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $category = ItemCategory::updateOrCreate([
            'company_id' => $company->id,
            'name' => 'Umum',
        ], [
            'description' => 'Kategori umum',
        ]);

        $unit = Unit::updateOrCreate([
            'company_id' => $company->id,
            'name' => 'PCS',
        ], [
            'symbol' => 'pcs',
        ]);

        Customer::updateOrCreate([
            'company_id' => $company->id,
            'name' => 'Pelanggan Contoh',
        ], [
            'company_name' => 'PT Pelanggan Contoh',
            'address' => 'Jl. Pelanggan No. 2',
            'phone' => '08123456780',
            'email' => 'customer@example.com',
        ]);

        Item::updateOrCreate([
            'company_id' => $company->id,
            'item_category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Barang Contoh',
        ], [
            'brand' => 'Brand A',
            'default_price' => 15000,
        ]);
    }
}
