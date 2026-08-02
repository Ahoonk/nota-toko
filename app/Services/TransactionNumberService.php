<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\CarbonInterface;

class TransactionNumberService
{
    public function generate(int $companyId, CarbonInterface $date): string
    {
        $prefix = 'TRX-'.$date->format('Ymd');
        $count = Transaction::query()
            ->where('company_id', $companyId)
            ->whereDate('transaction_date', $date->toDateString())
            ->count() + 1;

        return sprintf('%s-%05d', $prefix, $count);
    }
}
