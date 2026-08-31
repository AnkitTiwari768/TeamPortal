<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use Illuminate\Http\Resources\Json\JsonResource;

class BppIdListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'old_bpp_id' => null,
            'new_bpp_id' => $this->bpp_id,
            'updated_at' => $this->bpp_updated_at
                ? date('d-m-Y H:i:s', strtotime($this->bpp_updated_at))
                : null,
            'udyam_no' => $this->udyam_no,
        ];
    }
}
