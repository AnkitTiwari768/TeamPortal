<?php

declare(strict_types=1);

namespace App\Web\Workshop;

readonly class CreateWorkshopDTO
{
    /**
     * @param string $financialYear
     * @param string $duration
     * @param string|null $subDuration
     * @param string $eventTitle
     * @param array $eventFor
     * @param string $workshopCategory
     * @param string|null $remark
     * @param string $organiserName
     * @param string $branchOffice
     * @param array $startDate
     * @param array $endDate
     * @param array $startTime
     * @param string $eventDescription
     * @param string $workshopMode
     * @param string|null $conductedBy
     * @param string|null $targetAudience
     * @param string $venueAddress
     * @param string $stateId
     * @param string $districtId
     * @param string $subDistrictId
     * @param string|null $pincode
     * @param string|null $latitude
     * @param string|null $longitude
     * @param string $uploadedIds
     */
    public function __construct(
        public string $financialYear,
        public string $duration,
        public ?string $subDuration,
        public string $eventTitle,
        public array $eventFor,
        public string $workshopCategory,
        public ?string $remark,
        public string $organiserName,
        public string $branchOffice,
        public array $startDate,
        public array $endDate,
        public array $startTime,
        public string $eventDescription,
        public string $workshopMode,
        public ?string $conductedBy,
        public ?string $targetAudience,
        public string $venueAddress,
        public string $stateId,
        public string $districtId,
        public string $subDistrictId,
        public ?string $pincode,
        public ?string $latitude,
        public ?string $longitude,
        public string $uploadedIds
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            financialYear: $data['financial_year'],
            duration: $data['duration'],
            subDuration: $data['sub_duration'] ?? null,
            eventTitle: $data['event_title'],
            eventFor: $data['event_for'] ?? [],
            workshopCategory: $data['workshop_category'],
            remark: $data['remark'] ?? null,
            organiserName: $data['organiser_name'],
            branchOffice: $data['branch_office'],
            startDate: $data['start_date'] ?? [],
            endDate: $data['end_date'] ?? [],
            startTime: $data['start_time'] ?? [],
            eventDescription: $data['event_description'],
            workshopMode: $data['workshop_mode'],
            conductedBy: $data['conducted_by'] ?? null,
            targetAudience: $data['target_audience'] ?? null,
            venueAddress: $data['venue_address'],
            stateId: $data['state_id'],
            districtId: $data['district_id'],
            subDistrictId: $data['sub_district_id'],
            pincode: $data['pincode'] ?? null,
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
            uploadedIds: $data['uploaded_ids']
        );
    }

    public function toArray(): array
    {
        return [
            'financial_year'    => $this->financialYear,
            'duration'          => $this->duration,
            'sub_duration'      => $this->subDuration,
            'event_title'       => $this->eventTitle,
            'event_for'         => $this->eventFor,
            'workshop_category' => $this->workshopCategory,
            'remark'            => $this->remark,
            'organiser_name'    => $this->organiserName,
            'branch_office'     => $this->branchOffice,
            'start_date'        => $this->startDate,
            'end_date'          => $this->endDate,
            'start_time'        => $this->startTime,
            'event_description' => $this->eventDescription,
            'workshop_mode'     => $this->workshopMode,
            'conducted_by'      => $this->conductedBy,
            'target_audience'   => $this->targetAudience,
            'venue_address'     => $this->venueAddress,
            'state_id'          => $this->stateId,
            'district_id'       => $this->districtId,
            'sub_district_id'   => $this->subDistrictId,
            'pincode'           => $this->pincode,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'uploaded_ids'      => $this->uploadedIds,
        ];
    }
}
