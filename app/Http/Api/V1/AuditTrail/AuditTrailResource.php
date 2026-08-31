<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\AuditTrail;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditTrailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'module_name' => ucwords($this->module_name),
            'activity_type' => $this->activity_type,
            'full_name' => $this->first_name.' '.$this->middle_name.' '.$this->last_name,
            'email' => $this->email,  
            'last_login' => $this->last_login ? date('d-m-Y H:i:s',strtotime($this->last_login)) : '',  
            'ip_address' => $this->ip_address, 
            'created_at' => $this->created_at ? date('d-m-Y H:i:s',strtotime($this->created_at)) : '', 
        ];
    }
}