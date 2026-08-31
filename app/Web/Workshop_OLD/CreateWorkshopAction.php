<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use App\Web\Workshop\CreateWorkshopDTO;
use App\Web\Workshop\Workshop;
use Illuminate\Support\Facades\DB;

class CreateWorkshopAction
{
    /**
     * Execute the create workshop action.
     *
     * @param CreateWorkshopDTO $dto
     * @param string|null $eventId
     * @return Workshop
     */
    public function execute(CreateWorkshopDTO $dto, ?string $eventId = null): Workshop
    {
        $schedules = [];
        $isFirst = 0;
        $isLast = count($dto->startDate) - 1;
        $workshopStartDate = null;
        $workshopEndDate = null;
        foreach ($dto->startDate as $key => $start) {
            if ($isFirst === 0) {
                $formattedStart = $start ? \Carbon\Carbon::parse($start)->format('Y-m-d') : null;
                $workshopStartDate = $formattedStart;
                $isFirst = 1;
            }
            if ($key === $isLast) {
                $formattedEnd = ($dto->endDate[$key] ?? null) ? \Carbon\Carbon::parse($dto->endDate[$key])->format('Y-m-d') : null;
                $workshopEndDate = $formattedEnd;
            }
            $schedules[] = [
                'start_date' => $dto->startDate[$key] ?? null,
                'end_date'   => $dto->endDate[$key] ?? null,
                'start_time' => $dto->startTime[$key] ?? null,
            ];
        }

        $isNational = false;
        if ($dto->stateId) {
            $state = DB::table('states')->where('id', $dto->stateId)->first();
            if ($state && strtolower(trim($state->name)) === 'national') {
                $isNational = true;
            }
        }

        $eventData = [
            'event_title'       => $dto->eventTitle,
            'event_for'         => $dto->eventFor,
            'organiser_name'    => $dto->organiserName,
            'branch_offices_id' => $dto->branchOffice,
            'remark'            => $dto->remark,
            'venue_address'     => $dto->venueAddress,
            'schedules'         => $schedules,
            'event_description' => $dto->eventDescription,
            'state_id'          => $dto->stateId,
            'district_id'       => $isNational ? null : $dto->districtId,
            'sub_district_id'   => $dto->subDistrictId,
            'pincode'           => $dto->pincode,
            'latitude'          => $dto->latitude,
            'longitude'         => $dto->longitude,
            'uploaded_ids'      => $dto->uploadedIds,
            'financial_year'    => $dto->financialYear,
            'duration'          => $dto->duration,
            'sub_duration'      => $dto->subDuration,
            'workshop_category' => $dto->workshopCategory,
            'workshop_mode'     => $dto->workshopMode,
            'conducted_by'      => $dto->conductedBy,
            'target_audience'   => $dto->targetAudience,
            'workshop_start_date' => $workshopStartDate,
            'workshop_end_date' => $workshopEndDate,
        ];

        if (!$eventId) {
            $eventData['created_at'] = currentDateTime();
            $eventData['created_by'] = AuthId();
            $eventData['updated_at'] = currentDateTime();
            $eventData['updated_by'] = AuthId();

            return Workshop::create($eventData);
        }

        $eventData['updated_at'] = currentDateTime();
        $eventData['updated_by'] = AuthId();

        $workshop = Workshop::findOrFail($eventId);
        $workshop->fill($eventData)->save();

        return $workshop;
    }
}
