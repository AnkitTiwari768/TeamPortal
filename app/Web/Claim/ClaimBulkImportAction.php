<?php

namespace App\Web\Claim;

use App\Utils\UuidGenerator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Web\Claim\Claim;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithCustomValidationAttributes;


class ClaimBulkImportAction implements
    ToModel,
    WithBatchInserts,
    WithStartRow,
    WithValidation,
    WithMultipleSheets
{
    private int $rows = 0;

    private ?object $claimDetailInstance;

    public function __construct(
	    public array $pdfUploads,
        private string $fileName,
        private string $claimTypeId
    ) {
        $this->claimDetailInstance = $this->getClaimDetailsInstance();
    }

    public function getClaimDetailsInstance()
    {
        $claimType = $this->getClaimTypleSlug($this->claimTypeId);

        if ($claimType === 'claim-for-catalogue-creation') {
            return app(CatalogueCreationClaimDetails::class, [
                'claimService' => app(ClaimService::class),
                'claimTypeId' => $this->claimTypeId,
				'pdfUploads' => $this->pdfUploads,
            ]);
        } elseif ($claimType === 'claim-for-accounts-management') {
            return app(AccountManagementClaimDetails::class, [
                'claimService' => app(ClaimService::class),
                'claimTypeId' => $this->claimTypeId,
            ]);
        } elseif ($claimType === 'claim-for-logistics-and-transportation') {
            return app(AccountManagementClaimDetails::class, [
                'claimService' => app(ClaimService::class),
                'claimTypeId' => $this->claimTypeId,
            ]);
        }
    }

    // public function customValidationAttributes(): array
    // {
    //     return $this->getClaimDetailsInstance()->attributes();
    // }

    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }

    public function model(array $row)
    {
        ++$this->rows;

        $currentRow = $this->rows + 1;

        [$claimDetails, $claimOrders ,$claimDocuments] = $this->getClaimDetailsInstance()->data($row);
		
		

        if ($claimDetails && $claimOrders && $claimDocuments) {
            DB::transaction(function () use ($claimDetails, $claimOrders ,$claimDocuments) {
                DB::table('claims')->insert($claimDetails);
                DB::table('claim_orders')->insert($claimOrders);
				DB::table('claim_documents')->insert($claimDocuments);
            });
        }
		
		//dump($claimDocuments);
	}


    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function rules(): array
    {
        return $this->claimDetailInstance->rules();
    }

    public function customValidationAttributes()
    {
        return $this->claimDetailInstance->attributes();
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getClaimTypleSlug(string $claimTypeId): ?string
    {
        return DB::table('claim_types')->where('id', $claimTypeId)->value('slug');
    }
}
