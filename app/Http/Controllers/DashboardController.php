<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\Item;
use App\Models\PrintLog;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $companyId = auth()->user()?->company_id;

        $transactions = Transaction::query()->when($companyId, fn ($query) => $query->where('company_id', $companyId));
        $customers = Customer::query()->when($companyId, fn ($query) => $query->where('company_id', $companyId));
        $items = Item::query()->when($companyId, fn ($query) => $query->where('company_id', $companyId));
        $companies = Company::query();
        $templates = DocumentTemplate::query()->when($companyId, fn ($query) => $query->where('company_id', $companyId));

        return view('dashboard', [
            'stats' => [
                'transactions' => $transactions->count(),
                'customers' => $customers->count(),
                'items' => $items->count(),
                'companies' => $companies->count(),
                'documents' => PrintLog::query()->when($companyId, fn ($query) => $query->whereHas('transaction', fn ($t) => $t->where('company_id', $companyId)))->count(),
            ],
            'latestTransaction' => $transactions->latest('id')->first(),
            'latestDocument' => PrintLog::query()
                ->when($companyId, fn ($query) => $query->whereHas('transaction', fn ($t) => $t->where('company_id', $companyId)))
                ->latest('printed_at')
                ->with(['transaction', 'template', 'user'])
                ->first(),
            'latestAudit' => AuditLog::query()->latest('id')->first(),
            'templateCount' => $templates->count(),
        ]);
    }
}
