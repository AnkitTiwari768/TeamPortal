<?php

namespace App\Domain\DemandGeneration;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Carbon;

class DemandGenerationTransactionsImport implements ToArray, WithStartRow
{
    protected $aovType;
    protected $transactions = [];
    protected $uniqueMses = [];
    protected $totalAmount = 0;

    public function __construct(string $aovType)
    {
        $this->aovType = $aovType;
    }

    /**
     * Start reading from row 2 (skip headers)
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Process each row based on the template structure
     */
    public function array(array $rows)
    {
        $minOrderValue = $this->aovType === 'LOW' ? 100 : 1000;

        foreach ($rows as $row) {
            // Skip empty rows (check Network Transaction ID)
            if (empty($row[0])) {
                continue;
            }

            // Validate transaction is completed
            $transactionStatus = strtolower(trim($row[3] ?? ''));
            if ($transactionStatus !== 'yes' && $transactionStatus !== 'completed') {
                continue; // Skip incomplete transactions
            }

            // Parse MSME TEAM credential
            $hasMsmeCred = strtolower(trim($row[5] ?? '')) === 'yes';

            $transaction = [
                'network_transaction_id' => (string) $row[0] ?? '',
                'network_transaction_date' => $this->parseDate($row[1] ?? ''),
                'seller_id' => (string) $row[2] ?? '',
                'transaction_completed' => true,
                'order_invoice_number' => (string) $row[4] ?? '',
                'has_msme_team_cred' => $hasMsmeCred,
                'total_product_cost' => $this->parseAmount($row[6] ?? 0),
                'total_tax_on_product' => $this->parseAmount($row[7] ?? 0),
                'total_discount' => $this->parseAmount($row[8] ?? 0),
                'offers' => $this->parseAmount($row[9] ?? 0),
                'logistics_packaging' => $this->parseAmount($row[10] ?? 0),
                'tax_on_delivery_packaging' => $this->parseAmount($row[11] ?? 0),
                'misc_charges' => $this->parseAmount($row[12] ?? 0),
            ];

            // Calculate order value (excluding offers/discounts, including taxes/fees)
            $orderValue = $transaction['total_product_cost']
                + $transaction['total_tax_on_product']
                + $transaction['logistics_packaging']
                + $transaction['tax_on_delivery_packaging']
                + $transaction['misc_charges'];

            // Validate minimum order value
            if ($orderValue < $minOrderValue) {
                throw new \Exception(
                    "Transaction {$transaction['network_transaction_id']} " .
                        "(Invoice: {$transaction['order_invoice_number']}) " .
                        "does not meet minimum order value of ₹{$minOrderValue} for {$this->aovType} AOV. " .
                        "Calculated value: ₹{$orderValue}"
                );
            }

            // Track unique MSEs with MSME TEAM credential
            if ($transaction['has_msme_team_cred'] && !empty($transaction['seller_id'])) {
                $this->uniqueMses[$transaction['seller_id']] = true;
            }

            $this->transactions[] = $transaction;
            $this->totalAmount += $orderValue;
        }

        if (empty($this->transactions)) {
            throw new \Exception(
                'Excel file contains no valid completed transactions. ' .
                    'Please ensure transactions have "Completed" or "Yes" in Transaction Status column.'
            );
        }
    }

    /**
     * Parse date from Excel - handle multiple formats
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            throw new \Exception('Transaction date is required');
        }

        // If it's already a DateTime object (from Excel)
        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }

        // If it's Excel serial number
        if (is_numeric($date) && $date > 0) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
            } catch (\Exception $e) {
                // Try as regular number
            }
        }

        // Try to parse as date string
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format: {$date}. Please use YYYY-MM-DD format.");
        }
    }

    /**
     * Parse amount - convert to float safely
     */
    private function parseAmount($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.00;
        }

        // Remove currency symbols and commas
        $cleanValue = preg_replace('/[^\d.-]/', '', (string) $value);

        return (float) $cleanValue;
    }

    /**
     * Getter methods
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getUniqueMses(): array
    {
        return array_keys($this->uniqueMses);
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getTransactionCount(): int
    {
        return count($this->transactions);
    }
}
