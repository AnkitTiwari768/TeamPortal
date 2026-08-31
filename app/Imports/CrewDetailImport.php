<?php 

namespace App\Imports;

use App\Models\CrewDetail;
use App\Http\Api\V1\Country\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Traits\Respond;
use App\Exceptions\CustomExcelException;
use Closure;

class CrewDetailImport implements ToModel, WithBatchInserts, WithStartRow, WithValidation, WithMultipleSheets 
{
    use Respond;

    private string $_applicationId;
    private string $_fileName; 
    private $rows = 0;
 

    public function __construct(string $applicationId, $fileName)
    {
        $this->_applicationId = $applicationId;
        $this->_fileName = $fileName;
    }

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

        if (strtotime($row[12]) < strtotime($row[11])) {
            throw new CustomExcelException($currentRow);
        }

        $crewDetails = [
            'id' => uuid(),
            'name' => $row[1],
            'surname' => $row[2],
            'designation' => $row[3],
            'date_of_birth' => $row[4],
            'place_of_birth' => $row[5],
            'father_mother_name' => $row[6],
            'address_outside_india' => $row[7],
            'country_id' => Country::select('id')->where('slug', slugify($row[8]))->first()?->id,
            'nationality' => $row[8],
            'passport_number' => $row[9],
            'place_of_issue' => $row[10],
            'date_of_issue' => $row[11],
            'date_of_expiry' => $row[12]
        ];   

        return new CrewDetail([
            'id' => uuid(),
            'application_id' => $this->_applicationId,
            'crew_details_document' => $this->_fileName,
            'crew_details' => json_encode($crewDetails),
            'created_at' => currentDateTime(),
            'created_by' => AuthId()
        ]);
    }

     public function getRowCount(): int
    {
        return $this->rows;
    }

    public function rules(): array 
    {
        return [
            '1' => [
                'bail',
                'required',
                'string',
            ],
            '2' => [
                'bail',
                'nullable',
                'string',
            ],
            '3' => [
                'bail',
                'required',
                'string'
            ],
            '4' => [
                'bail',
                'required',
                'date',
            ],
            '5' => [
                'bail',
                'nullable',
                'string',
            ],
            '6' => [
                'bail',
                'nullable',
                'string',
            ],
            '7' => [
                'bail',
                'required',
                'string'
            ],
            '8' => [
                'bail',
                'required',
                'string'
            ],
            '9' => [
                'bail',
                'required'                
            ],
            '10' => [
                'bail',
                'required',
            ],
            '11' => [
                'bail',
                'required',
                'date'
            ],  
            '12' => [
                'bail',
                'required',
                'date'
            ], 
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function startRow(): int
    {
        return 2;
    }
}