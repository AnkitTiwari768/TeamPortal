<?php

declare(strict_types=1);
namespace App\Traits;

trait HasClaimNetAmountPayable
{

    protected function calculateNetAmountPayable(
        float $base = 0.00, 
        float $gst = 0.00, 
        float $tds = 0.00, 
        float $sgst = 0.00, 
        float $cgst = 0.00, 
        float $tds_cgst = 0.00, 
        float $tds_sgst = 0.00
    )
    {
        return ($base - $gst - $sgst - $cgst) + $gst + $cgst + $sgst - ($tds + $tds_cgst + $tds_sgst);
    }
}