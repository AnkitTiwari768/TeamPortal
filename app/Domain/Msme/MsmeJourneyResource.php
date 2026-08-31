<?php

namespace App\Domain\Msme;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class MsmeJourneyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'entrepreneur_name' => $this->entrepreneur_name,
            'team_id'           => $this->team_id,
            'sdasd' => 'asdasd',
            'lead_status'       => $this->snp_name ? 'Pending at ' . $this->snp_name : 'Open to SNP(s)',
            'mapped_at'         => Carbon::parse($this->created_at)->format('d M Y, h:i A'),
            'onboard_at'        => Carbon::parse($this->created_at)->format('d M Y, h:i A'),
            'claims_exists'     => (bool) $this->claims_exists,
        ];
    }
}
