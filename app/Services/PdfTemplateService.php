<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\PrintLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class PdfTemplateService
{
    public function render(Transaction $transaction, string $documentType, bool $preview = false): array
    {
        $template = DocumentTemplate::query()
            ->where('company_id', $transaction->company_id)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $template) {
            throw new RuntimeException("Template {$documentType} belum diunggah.");
        }

        $templatePath = Storage::disk('public')->path($template->template_path);
        if (! is_file($templatePath)) {
            throw new RuntimeException("File template untuk {$documentType} tidak ditemukan.");
        }

        $outputName = sprintf(
            '%s/%s-%s-%s.pdf',
            'generated',
            $transaction->transaction_number,
            $documentType,
            $preview ? 'preview' : 'print'
        );

        Storage::disk('local')->makeDirectory('generated');
        $outputPath = Storage::disk('local')->path($outputName);

        $pdf = new Fpdi();
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetCompression(false);
        $pageCount = $pdf->setSourceFile($templatePath);

        for ($page = 1; $page <= $pageCount; $page++) {
            $templateId = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($templateId);
            $orientation = ($size['width'] ?? 0) > ($size['height'] ?? 0) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            if ($page === 1) {
                $this->writeFixedLayout($pdf, $transaction, (float) ($size['width'] ?? 210.11), (float) ($size['height'] ?? 150.14));
            }
        }

        $pdf->Output('F', $outputPath);

        PrintLog::create([
            'transaction_id' => $transaction->id,
            'document_template_id' => $template->id,
            'printed_by' => auth()->id(),
            'document_type' => $documentType,
            'template_path' => $template->template_path,
            'generated_path' => $outputName,
            'status' => $preview ? 'preview' : 'printed',
            'metadata' => [
                'transaction_number' => $transaction->transaction_number,
                'document_type' => $documentType,
                'generated_at' => now()->toIso8601String(),
            ],
            'printed_at' => now(),
        ]);

        return [
            'path' => $outputPath,
            'file_name' => basename($outputPath),
        ];
    }

    protected function writeFixedLayout(Fpdi $pdf, Transaction $transaction, float $pageWidth, float $pageHeight): void
    {
        $scaleX = $pageWidth / 210.11;
        $scaleY = $pageHeight / 150.14;

        $x = fn (float $value): float => $value * $scaleX;
        $y = fn (float $value): float => $value * $scaleY;
        $w = fn (float $value): float => $value * $scaleX;

        $contentShiftY = 12.0 * $scaleY;
        $topY = $y(30) + $contentShiftY;
        $this->labelLine($pdf, 'Perusahaan', $transaction->company?->name ?? '-', $x(8), $topY - 5, $w(105), 8.8);
        $this->labelLine($pdf, 'Customer', $transaction->customer_name, $x(8), $topY, $w(105), 8.8);
        $this->multiLine($pdf, 'Alamat', $transaction->customer?->address ?: '-', $x(8), $topY + 5, $w(105), 4.0, 8.8);

        $this->labelLine($pdf, 'Nomor', $transaction->transaction_number, $x(122), $topY, $w(80), 8.8);
        $this->labelLine($pdf, 'Tanggal', $transaction->transaction_date?->format('d F Y') ?? '-', $x(122), $topY + 5, $w(80), 8.8);

        $itemHeaderY = $topY + 22;
        $itemsBottomY = $this->writeItems($pdf, $transaction->details, $x, $itemHeaderY);
        $summaryY = $itemsBottomY + 2;
        $this->writeSummary($pdf, $transaction, $x, $summaryY);
        $termsBottomY = $this->writeTerms($pdf, $transaction, $x, $summaryY);
        $this->writeQrisBlock($pdf, $transaction, $x, $termsBottomY + 8);
        $this->writeSignatureBlock($pdf, $pageWidth, $pageHeight);
    }

    protected function writeTerms(Fpdi $pdf, Transaction $transaction, callable $x, float $topY): float
    {
        $pdf->SetFont('Helvetica', 'I', 8.4);
        $pdf->SetXY($x(8), $topY - 0.2);
        $pdf->Cell($x(105), 4, 'terbilang :', 0, 0, 'L');
        $pdf->SetXY($x(8), $topY + 3.3);
        $pdf->MultiCell($x(105), 4, $transaction->words ?? '-', 0, 'L');

        return $pdf->GetY();
    }

    protected function writeQrisBlock(Fpdi $pdf, Transaction $transaction, callable $x, float $topY): void
    {
        $qrRelativePath = 'qris/qris-perusahaan.png';
        if (! Storage::disk('public')->exists($qrRelativePath)) {
            return;
        }

        $qrPath = Storage::disk('public')->path($qrRelativePath);
        $qrSize = 26.0;
        $qrX = $x(8);
        $qrY = $topY;

        $pdf->SetFont('Arial', 'B', 8.8);
        $pdf->SetXY($qrX, $qrY - 4.5);
        $pdf->Cell($qrSize + 8, 4, 'payment to :', 0, 0, 'L');
        $pdf->Image($qrPath, $qrX, $qrY, $qrSize, $qrSize, 'PNG');
        $pdf->SetFont('Arial', 'B', 8.8);
        $pdf->SetXY($qrX, $qrY + $qrSize + 1.2);
        $pdf->Cell($qrSize + 8, 4, 'ALDERA TECH', 0, 0, 'L');
    }

    protected function writeSignatureBlock(Fpdi $pdf, float $pageWidth, float $pageHeight): void
    {
        $qrText = 'Bayu Suderajat, S.Kom.';
        $qrSize = 18.0;
        $rightMargin = 11.0;
        $bottomMargin = 7.0;
        $qrX = $pageWidth - $rightMargin - $qrSize;
        $qrY = $pageHeight - $bottomMargin - $qrSize;

        $qrPath = $this->generateSignatureQr($qrText);

        $pdf->SetFont('Arial', 'B', 8.6);
        $pdf->SetXY($qrX - 3, $qrY - 5.5);
        $pdf->Cell($qrSize + 6, 4, 'Hormat Kami', 0, 0, 'C');
        $pdf->Image($qrPath, $qrX, $qrY, $qrSize, $qrSize, 'PNG');
    }

    protected function generateSignatureQr(string $text): string
    {
        $directory = 'generated/qrcodes';
        Storage::disk('local')->makeDirectory($directory);

        $fileName = 'signature-'.sha1($text).'.png';
        $path = Storage::disk('local')->path($directory.'/'.$fileName);

        if (! is_file($path)) {
            $result = (new Builder(
                data: $text,
                size: 240,
                margin: 0,
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            ))->build();

            $result->saveToFile($path);
        }

        return $path;
    }

    protected function writeItems(Fpdi $pdf, Collection $details, callable $x, float $startY): float
    {
        $rowHeight = 8.8;
        $headerHeight = 10.0;
        $visibleRows = $details->values();

        if ($visibleRows->isEmpty()) {
            $visibleRows->push((object) [
                'item_name' => '',
                'brand' => '',
                'replacement_item_name' => '',
                'qty' => '',
                'price' => '',
                'total' => '',
            ]);
        }

        $columns = [
            ['label' => 'No', 'width' => 10],
            ['label' => 'Item', 'width' => 92],
            ['label' => 'Qty', 'width' => 18],
            ['label' => 'Harga', 'width' => 35],
            ['label' => 'Jumlah', 'width' => 37],
        ];

        $tableWidth = array_sum(array_column($columns, 'width'));
        $bodyHeight = $rowHeight * $visibleRows->count();
        $this->drawTableFrame($pdf, $x(8), $startY, $tableWidth, $headerHeight + $bodyHeight, $columns, $headerHeight, $bodyHeight);
        $this->drawItemBodyGrid($pdf, $x(8), $startY + $headerHeight, $tableWidth, $rowHeight, $visibleRows->count(), $columns);

        $pdf->SetFont('Arial', '', 8.5);
        foreach ($visibleRows as $index => $detail) {
            $top = $startY + $headerHeight + ($index * $rowHeight);
            if (! empty($detail->item_name)) {
                $pdf->SetXY($x(8), $top);
                $pdf->Cell($x(10), 4, (string) ($index + 1), 0, 0, 'C');

                $mainItem = $this->itemLine($detail);
                $subItem = $this->subItemLine($detail);
                $this->writeText($pdf, $x(20), $top, $x(84), 4, $mainItem, 8.5, 'Arial', '');
                if ($subItem !== null) {
                    $this->writeText($pdf, $x(20), $top + 4, $x(84), 4, $subItem, 7.2, 'Arial', 'I');
                }

                $qty = (string) ($detail->qty ?? '');
                $price = ($detail->price === '' || $detail->price === null) ? '' : 'Rp '.number_format((float) $detail->price, 0, ',', '.');
                $total = ($detail->total === '' || $detail->total === null) ? '' : 'Rp '.number_format((float) $detail->total, 0, ',', '.');

                $pdf->SetXY($x(108), $top);
                $pdf->Cell($x(18), 4, $qty, 0, 0, 'C');
                $pdf->SetXY($x(126), $top);
                $pdf->Cell($x(35), 4, $price, 0, 0, 'C');
                $pdf->SetXY($x(165), $top);
                $pdf->Cell($x(37), 4, $total, 0, 0, 'C');
            }
        }

        return $startY + $headerHeight + ($rowHeight * $visibleRows->count());
    }

    protected function drawItemBodyGrid(Fpdi $pdf, float $x, float $y, float $width, float $rowHeight, int $rowCount, array $columns): void
    {
        if ($rowCount <= 0) {
            return;
        }

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);

        for ($row = 0; $row < $rowCount; $row++) {
            $rowY = $y + ($rowHeight * $row);
            $cursor = $x;

            foreach ($columns as $column) {
                $pdf->Rect($cursor, $rowY, $column['width'], $rowHeight);
                $cursor += $column['width'];
            }
        }
    }

    protected function writeSummary(Fpdi $pdf, Transaction $transaction, callable $x, float $summaryY): void
    {
        $baseX = $x(138);
        $valueX = $x(177);
        $valueWidth = $x(37);
        $lineHeight = 4.6;

        $pdf->SetFont('Arial', '', 8.2);
        $this->writeSummaryRow($pdf, $baseX, $summaryY, $valueX, $valueWidth, 'Subtotal :', 'Rp '.number_format((float) $transaction->subtotal, 0, ',', '.'));
        $this->writeSummaryRow($pdf, $baseX, $summaryY + $lineHeight, $valueX, $valueWidth, 'Diskon :', 'Rp '.number_format((float) $transaction->discount_total, 0, ',', '.'));
        $pdf->SetFont('Arial', 'B', 8.2);
        $this->writeSummaryRow($pdf, $baseX, $summaryY + ($lineHeight * 2), $valueX, $valueWidth, 'Total :', 'Rp '.number_format((float) $transaction->grand_total, 0, ',', '.'));
    }

    protected function writeSummaryRow(Fpdi $pdf, float $x, float $y, float $valueX, float $valueWidth, string $label, string $value): void
    {
        $textY = $y - 0.2;

        $pdf->SetXY($x, $textY);
        $pdf->Cell(40, 3.1, $label, 0, 0, 'L');
        $pdf->Cell(4, 3.1, ':', 0, 0, 'C');
        $pdf->SetXY($valueX, $textY);
        $pdf->Cell($valueWidth, 3.1, $value, 0, 0, 'L');
    }

    protected function drawDashedLine(Fpdi $pdf, float $x1, float $y1, float $x2, float $dashLength = 1.8, float $gapLength = 1.0): void
    {
        $pdf->SetDrawColor(80, 80, 80);
        $cursor = $x1;

        while ($cursor < $x2) {
            $end = min($cursor + $dashLength, $x2);
            $pdf->Line($cursor, $y1, $end, $y1);
            $cursor = $end + $gapLength;
        }
    }

    protected function drawTableFrame(Fpdi $pdf, float $x, float $y, float $width, float $height, array $columns, float $headerHeight, float $bodyHeight): void
    {
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);
        $pdf->Rect($x, $y, $width, $height);
        $pdf->Line($x, $y + $headerHeight, $x + $width, $y + $headerHeight);

        $cursor = $x;
        foreach (array_slice($columns, 0, -1) as $column) {
            $cursor += $column['width'];
            $pdf->Line($cursor, $y, $cursor, $y + $height);
        }

        $pdf->SetFillColor(245, 245, 245);
        $pdf->Rect($x, $y, $width, $headerHeight, 'F');

        $pdf->SetFont('Arial', 'B', 8.5);
        $cursor = $x;
        foreach ($columns as $column) {
            $pdf->Rect($cursor, $y, $column['width'], $headerHeight);
            $pdf->SetXY($cursor, $y);
            $pdf->Cell($column['width'], $headerHeight, $this->toPdfText($column['label']), 0, 0, 'C');
            $cursor += $column['width'];
        }
    }

    protected function writeText(Fpdi $pdf, float $x, float $y, float $width, float $height, string $text, float $size, string $fontFamily, string $style = '', string $align = 'L'): void
    {
        if ($text === '') {
            return;
        }

        $pdf->SetFont($fontFamily, $style, $size);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $height, $text, 0, 0, $align);
    }

    protected function writeMultiLine(Fpdi $pdf, float $x, float $y, float $width, float $lineHeight, string $text, float $size, string $fontFamily, string $style = ''): void
    {
        if ($text === '') {
            return;
        }

        $pdf->SetFont($fontFamily, $style, $size);
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($width, $lineHeight, $text, 0, 'L');
    }

    protected function writeLabelValue(Fpdi $pdf, float $x, float $y, float $labelWidth, float $valueWidth, string $label, string $value, float $valueSize, bool $multiline = false): float
    {
        $pdf->SetFont('Helvetica', '', 8.2);
        $pdf->SetXY($x, $y);
        $pdf->Cell($labelWidth, 4, $label, 0, 0, 'L');
        $pdf->SetXY($x + $labelWidth + 2.5, $y);
        $pdf->SetFont('Helvetica', '', $valueSize);
        if ($multiline) {
            $pdf->MultiCell($valueWidth, 4, $value, 0, 'L');
            return $pdf->GetY();
        } else {
            $pdf->Cell($valueWidth, 4, $value, 0, 0, 'L');
            return $y + 4;
        }
    }

    protected function labelLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width, float $fontSize = 8.8): void
    {
        $pdf->SetFont('Arial', '', $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell(24, 4.2, $this->toPdfText($label), 0, 0, 'L');
        $pdf->Cell(4, 4.2, ':', 0, 0, 'L');
        $pdf->Cell($width - 28, 4.2, $this->toPdfText($value), 0, 0, 'L');
    }

    protected function multiLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width, float $lineHeight, float $fontSize): void
    {
        $pdf->SetFont('Arial', '', $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell(24, $lineHeight, $this->toPdfText($label), 0, 0, 'L');
        $pdf->Cell(4, $lineHeight, ':', 0, 0, 'L');
        $pdf->MultiCell($width - 28, $lineHeight, $this->toPdfText($value), 0, 'L');
    }

    protected function toPdfText(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '-';
        }

        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $value);

        return $converted !== false ? $converted : $value;
    }

    protected function itemLine(object $detail): string
    {
        $parts = array_filter([
            $detail->item_name,
            $detail->brand,
        ]);

        return implode(' - ', $parts);
    }

    protected function subItemLine(object $detail): ?string
    {
        if (! $detail->replacement_item_name) {
            return null;
        }

        return 'Sub item: '.$detail->replacement_item_name;
    }
}
