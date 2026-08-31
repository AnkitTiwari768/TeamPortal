<?php

namespace App\Http\Api\V1\AdminWorkshop;
use Carbon\Carbon;

class AdminWorkshopDTO
{
    public function __construct(
        // Mandatory workshop fields
        public readonly string  $financialYear,
        public readonly string  $duration,
        public readonly string  $title,             // from: workshop_title
        public readonly string  $workshopDate,      // from: workshop_date

        // Location
        public readonly ?string $stateId      = null,
        public readonly ?string $districtId   = null,
        public readonly ?string $venue        = null,  // from: venue (was venue_address)

        // Workshop details
        public readonly ?string $workshopMode       = null,
        public readonly ?string $organizerName      = null,  // from: organizer_name
        public readonly ?string $conductedBy        = null,
        public readonly ?string $targetAudience     = null,
        public readonly ?int    $numberOfParticipants = null,
        public readonly ?string $workshopDescription = null, // from: workshop_description
        public readonly ?string $remarks            = null,  // from: remarks
        public readonly ?string $uploadedIds        = null,
        public readonly string  $status             = 'draft',

        // Expense fields (collected in same form)
        public readonly ?float  $expenseAmount      = null,
        public readonly int     $tdsApplicable      = 0,     // 1=Yes, 0=No
        public readonly float   $tdsPercentage      = 0.00,
        public readonly ?string $sanctionOrderNumber = null, // from: sanction_order_number
        public readonly ?string $sanctionOrderDate  = null,
        public readonly ?string $netAmount           = null,  // Calculated as: expenseAmount - (expenseAmount * tdsPercentage / 100)
        public readonly ?string $subDuration = null,
        // Schedule
        public readonly array   $schedules          = [],

        public readonly ?float   $nsic_fees      = 0.00,
        public readonly ?string $sub_district_id = null,
        public readonly ?string $branch_office = null,
        public readonly ?string $conducted_by_other = null,

    ) {}

    public static function fromRequest(AdminWorkshopRequest $request): self
    {
        return new self(
            financialYear:        $request->validated('financial_year'),
            duration:             $request->validated('duration'),
            title:                $request->validated('workshop_title'),       // FIXED key
            workshopDate:         $request->validated('workshop_date')
                                    ? Carbon::createFromFormat('d-m-Y', $request->validated('workshop_date'))->format('Y-m-d')
                                    : null,
            stateId:              $request->validated('state_id'),
            districtId:           $request->validated('district_id'),
            venue:                $request->validated('venue'),                // FIXED key
            workshopMode:         $request->validated('workshop_mode'),
            organizerName:        $request->validated('organizer_name'),       // FIXED key
            conductedBy:          $request->validated('conducted_by'),
            targetAudience:       $request->validated('target_audience'),
            numberOfParticipants: $request->validated('number_of_participants')
                                    ? (int) $request->validated('number_of_participants')
                                    : null,
            workshopDescription:  $request->validated('workshop_description'), // FIXED key
            remarks:              $request->validated('remarks'),              // FIXED key
            uploadedIds:          $request->validated('uploaded_ids'),
            status:               $request->validated('status', 'draft'),
            expenseAmount:        $request->validated('expense_amount')
                                    ? (float) $request->validated('expense_amount')
                                    : null,
            tdsApplicable:        (int) $request->validated('tds_applicable', 0),
            tdsPercentage:        (float) $request->validated('tds_percentage', 0),
            netAmount:           $request->validated('expense_amount') && $request->validated('tds_percentage')
                                    ? (float) $request->validated('expense_amount') - 
                                      ((float) $request->validated('expense_amount') * (float) $request->validated('tds_percentage') / 100)
                                    : null,
            sanctionOrderNumber:  $request->validated('sanction_order_number'),
            sanctionOrderDate:   $request->validated('sanction_order_date')
                                        ? Carbon::createFromFormat('d-m-Y', $request->validated('sanction_order_date'))->format('Y-m-d')
                                        : null,
            schedules:            $request->validated('schedules', []),
            subDuration:        $request->validated('sub_duration'),


            nsic_fees:        $request->validated('nsic_fees'),
            conducted_by_other:        $request->validated('conducted_by_other'),
            sub_district_id:        $request->validated('sub_district_id'),
            branch_office:        $request->validated('branch_office'),
        );
    }
}
