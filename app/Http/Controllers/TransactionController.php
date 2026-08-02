<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Transaction;
use App\Services\AuditLogService;
use App\Services\PdfTemplateService;
use App\Services\TransactionNumberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $transactions = Transaction::query()
            ->with(['company', 'customer', 'details'])
            ->latest('id')
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        return view('transactions.form', $this->formData());
    }

    public function edit(Transaction $transaction): View
    {
        $transaction->load(['details', 'company', 'customer', 'details.item', 'details.unit', 'broughtItems.item']);

        return view('transactions.form', array_merge($this->formData(), [
            'transaction' => $transaction,
        ]));
    }

    public function store(Request $request, TransactionNumberService $numberService, AuditLogService $auditLogService): \Illuminate\Http\RedirectResponse
    {
        $data = $this->validateTransactionRequest($request);

        $transactionDate = Carbon::parse($data['transaction_date']);
        $company = Company::query()->findOrFail($data['company_id']);
        $customer = Customer::query()->findOrFail($data['customer_id']);
        $transactionNumber = $numberService->generate($company->id, $transactionDate);

        $userId = $request->user()?->id;

        $transaction = DB::transaction(function () use ($data, $company, $customer, $transactionNumber, $transactionDate, $userId) {
            $subtotal = 0;
            $details = [];

            foreach ($data['details'] as $index => $row) {
                $lineTotal = ((float) $row['qty'] * (float) $row['price']) - (float) ($row['discount'] ?? 0);
                $subtotal += $lineTotal;
                $details[] = [
                    'item_id' => $row['item_id'] ?? null,
                    'unit_id' => $row['unit_id'] ?? null,
                    'item_name' => $row['item_name'],
                    'item_category_name' => $row['item_category_name'] ?? null,
                    'brand' => $row['brand'] ?? null,
                    'replacement_item_name' => $row['replacement_item_name'] ?? null,
                    'qty' => $row['qty'],
                    'unit_name' => $row['unit_name'],
                    'price' => $row['price'],
                    'modal' => $row['modal'] ?? 0,
                    'discount' => $row['discount'] ?? 0,
                    'total' => $lineTotal,
                    'sort_order' => $index,
                    'notes' => null,
                ];
            }

            $discountTotal = (float) ($data['discount_total'] ?? 0);
            $taxTotal = (float) ($data['tax_total'] ?? 0);
            $grandTotal = $subtotal - $discountTotal + $taxTotal;

            $transaction = Transaction::create([
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'created_by' => $userId,
                'updated_by' => $userId,
                'transaction_number' => $transactionNumber,
                'transaction_date' => $transactionDate->toDateString(),
                'customer_name' => $customer->name,
                'company_name' => $company->name,
                'description' => $data['description'] ?? null,
                'work_type' => $data['work_type'] ?? null,
                'work_location' => $data['work_location'] ?? null,
                'work_duration' => $data['work_duration'] ?? null,
                'brought_item_id' => $this->primaryBroughtItemId($data['brought_items'] ?? []),
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'words' => $this->terbilang((int) round($grandTotal)).' rupiah',
                'notes' => $data['notes'] ?? null,
                'status' => 'belum dibayar',
                'template_snapshot' => [
                    'notes' => null,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            $transaction->details()->createMany($details);
            $this->syncBroughtItems($transaction, $data['brought_items'] ?? []);

            return $transaction->load(['details', 'broughtItems']);
        });

        $auditLogService->record($request, $transaction, 'created', [], $transaction->toArray());

        return redirect()->route('transactions.show', $transaction)->with('status', 'Transaksi berhasil disimpan.');
    }

    public function update(Request $request, Transaction $transaction, AuditLogService $auditLogService): \Illuminate\Http\RedirectResponse
    {
        $transaction->load(['details', 'broughtItems']);
        $data = $this->validateTransactionRequest($request);
        $oldValues = $transaction->toArray();

        DB::transaction(function () use ($request, $transaction, $data) {
            $company = Company::query()->findOrFail($data['company_id']);
            $customer = Customer::query()->findOrFail($data['customer_id']);

            $subtotal = 0;
            $details = [];

            foreach ($data['details'] as $index => $row) {
                $lineTotal = ((float) $row['qty'] * (float) $row['price']) - (float) ($row['discount'] ?? 0);
                $subtotal += $lineTotal;
                $details[] = [
                    'item_id' => $row['item_id'] ?? null,
                    'unit_id' => $row['unit_id'] ?? null,
                    'item_name' => $row['item_name'],
                    'item_category_name' => $row['item_category_name'] ?? null,
                    'brand' => $row['brand'] ?? null,
                    'replacement_item_name' => $row['replacement_item_name'] ?? null,
                    'qty' => $row['qty'],
                    'unit_name' => $row['unit_name'],
                    'price' => $row['price'],
                    'modal' => $row['modal'] ?? 0,
                    'discount' => $row['discount'] ?? 0,
                    'total' => $lineTotal,
                    'sort_order' => $index,
                    'notes' => null,
                ];
            }

            $discountTotal = (float) ($data['discount_total'] ?? 0);
            $taxTotal = (float) ($data['tax_total'] ?? 0);
            $grandTotal = $subtotal - $discountTotal + $taxTotal;

            $transaction->forceFill([
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'updated_by' => $request->user()?->id,
                'transaction_date' => Carbon::parse($data['transaction_date'])->toDateString(),
                'customer_name' => $customer->name,
                'company_name' => $company->name,
                'description' => $data['description'] ?? null,
                'work_type' => $data['work_type'] ?? null,
                'work_location' => $data['work_location'] ?? null,
                'work_duration' => $data['work_duration'] ?? null,
                'brought_item_id' => $this->primaryBroughtItemId($data['brought_items'] ?? []),
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'words' => $this->terbilang((int) round($grandTotal)).' rupiah',
                'notes' => $data['notes'] ?? null,
                'printed_at' => null,
                'printed_by' => null,
            ])->save();

            $transaction->details()->delete();
            $transaction->details()->createMany($details);
            $transaction->broughtItems()->delete();
            $this->syncBroughtItems($transaction, $data['brought_items'] ?? []);
        });

        $auditLogService->record($request, $transaction, 'updated', $oldValues, $transaction->fresh()->toArray());

        return redirect()->route('transactions.show', $transaction)->with('status', 'Transaksi berhasil diperbarui.');
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['company', 'customer', 'details', 'printer', 'broughtItem', 'broughtItems.item']);

        return view('transactions.show', [
            'transaction' => $transaction,
            'documentTypes' => config('nota_toko.document_types'),
        ]);
    }

    public function markPaid(Request $request, Transaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $transaction->forceFill([
            'status' => 'sudah dibayar',
            'updated_by' => $request->user()?->id,
        ])->save();

        return redirect()->route('transactions.show', $transaction)->with('status', 'Status transaksi sudah dibayar.');
    }

    public function document(Request $request, Transaction $transaction, string $type, PdfTemplateService $pdfTemplateService)
    {
        abort_unless(array_key_exists($type, config('nota_toko.document_types')), 404);
        $preview = str_contains($request->route()?->getName() ?? '', 'preview');
        $result = $pdfTemplateService->render($transaction->load(['company', 'customer', 'details']), $type, $preview);

        if (! $preview) {
            $transaction->forceFill([
                'printed_at' => now(),
                'printed_by' => $request->user()?->id,
            ])->save();
        }

        return $preview
            ? response()->file($result['path'], [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ])
            : response()->download($result['path'], $result['file_name']);
    }

    protected function formData(): array
    {
        return [
            'companies' => Company::query()->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('name')->get(),
            'items' => Item::query()->with(['category', 'unit'])->orderBy('name')->get(),
            'documentTypes' => config('nota_toko.document_types'),
        ];
    }

    protected function validateTransactionRequest(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'work_type' => ['nullable', 'in:Perbaikan,Pemeliharaan,Pembelian'],
            'work_location' => ['nullable', 'in:Kantor,Workshop'],
            'work_duration' => ['nullable', 'in:1 Hari,3 Hari,5 Hari,7 Hari'],
            'brought_item_id' => ['nullable', 'exists:items,id'],
            'brought_items' => ['nullable', 'array'],
            'brought_items.*.item_id' => ['nullable', 'exists:items,id'],
            'brought_items.*.notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'discount_total' => ['nullable', 'numeric'],
            'tax_total' => ['nullable', 'numeric'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.item_id' => ['nullable', 'exists:items,id'],
            'details.*.item_name' => ['required', 'string'],
            'details.*.item_category_name' => ['nullable', 'string'],
            'details.*.brand' => ['nullable', 'string'],
            'details.*.replacement_item_name' => ['nullable', 'string'],
            'details.*.qty' => ['required', 'numeric', 'min:0.01'],
            'details.*.unit_id' => ['nullable', 'exists:units,id'],
            'details.*.unit_name' => ['required', 'string'],
            'details.*.price' => ['required', 'numeric', 'min:0'],
            'details.*.modal' => ['nullable', 'numeric', 'min:0'],
            'details.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    protected function primaryBroughtItemId(array $broughtItems): ?int
    {
        foreach ($broughtItems as $row) {
            if (! empty($row['item_id'])) {
                return (int) $row['item_id'];
            }
        }

        return null;
    }

    protected function syncBroughtItems(Transaction $transaction, array $broughtItems): void
    {
        $rows = [];

        foreach ($broughtItems as $index => $row) {
            if (empty($row['item_id'])) {
                continue;
            }

            $item = Item::query()->find($row['item_id']);
            $rows[] = [
                'item_id' => $item?->id,
                'item_name' => $item?->name,
                'notes' => $row['notes'] ?? null,
                'sort_order' => $index,
            ];
        }

        if (! empty($rows)) {
            $transaction->broughtItems()->createMany($rows);
        }
    }

    protected function terbilang(int $number): string
    {
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return trim($words[$number]);
        }

        if ($number < 20) {
            return $this->terbilang($number - 10).' belas';
        }

        if ($number < 100) {
            return $this->terbilang(intval($number / 10)).' puluh '.$this->terbilang($number % 10);
        }

        if ($number < 200) {
            return 'seratus '.$this->terbilang($number - 100);
        }

        if ($number < 1000) {
            return $this->terbilang(intval($number / 100)).' ratus '.$this->terbilang($number % 100);
        }

        if ($number < 2000) {
            return 'seribu '.$this->terbilang($number - 1000);
        }

        if ($number < 1000000) {
            return $this->terbilang(intval($number / 1000)).' ribu '.$this->terbilang($number % 1000);
        }

        if ($number < 1000000000) {
            return $this->terbilang(intval($number / 1000000)).' juta '.$this->terbilang($number % 1000000);
        }

        return (string) $number;
    }
}
