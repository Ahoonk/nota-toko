<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\PrintLog;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\PdfTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_a_fallback_template_when_the_matching_company_template_is_missing(): void
    {
        $companyA = Company::create(['name' => 'Company A']);
        $companyB = Company::create(['name' => 'Company B']);
        $customer = Customer::create([
            'company_id' => $companyA->id,
            'name' => 'Customer A',
            'company_name' => 'Customer A Ltd',
        ]);

        $transaction = Transaction::create([
            'company_id' => $companyA->id,
            'customer_id' => $customer->id,
            'transaction_number' => 'TRX-TEST-0001',
            'transaction_date' => now()->toDateString(),
            'customer_name' => $customer->name,
            'company_name' => $companyA->name,
            'subtotal' => 100000,
            'discount_total' => 0,
            'tax_total' => 0,
            'grand_total' => 100000,
            'words' => 'seratus ribu rupiah',
            'status' => 'belum dibayar',
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'item_name' => 'Jasa Service',
            'qty' => 1,
            'unit_name' => 'PCS',
            'price' => 100000,
            'discount' => 0,
            'total' => 100000,
            'sort_order' => 0,
        ]);

        $templatePath = 'templates/fallback-template.pdf';
        Storage::disk('public')->makeDirectory('templates');
        $this->createBlankPdf(Storage::disk('public')->path($templatePath));

        $fallbackTemplate = DocumentTemplate::create([
            'company_id' => $companyB->id,
            'document_type' => 'kuitansi',
            'name' => 'Fallback Kuitansi',
            'template_path' => $templatePath,
            'is_active' => true,
        ]);

        $result = app(PdfTemplateService::class)->render($transaction->load(['company', 'customer', 'details']), 'kuitansi', true);

        $this->assertFileExists($result['path']);
        $this->assertTrue(Storage::disk('local')->exists('generated/TRX-TEST-0001-kuitansi-preview.pdf'));
        $this->assertDatabaseHas('print_logs', [
            'transaction_id' => $transaction->id,
            'document_template_id' => $fallbackTemplate->id,
            'document_type' => 'kuitansi',
            'status' => 'preview',
        ]);
    }

    private function createBlankPdf(string $path): void
    {
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, 'Template');
        $pdf->Output('F', $path);
    }
}
