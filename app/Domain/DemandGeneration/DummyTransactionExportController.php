<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use Maatwebsite\Excel\Facades\Excel;

class DummyTransactionExportController
{
    public function exportDummyCsv(int $numTeams = 5, int $numTransactions = 50)
    {
        return Excel::download(
            new DummyTransactionsExport($numTeams, $numTransactions),
            'demand_generation.csv'
        );
    }
}
