<?php 

declare(strict_types=1);

namespace  App\Http\Api\V1\PMRegistration;

use Illuminate\Http\Resources\Json\JsonResource;

class PMRegistrationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
        'id' => $this->id,
        'owner_name' => $this->owner_name,
        'store_name' => $this->store_name,
        'mobile' => $this->mobile,
        'email' => $this->email,
        //'pan_no' => $this->pan_no,
        'pin_code' => $this->pin_code,
        'address' => $this->address,

        'type_of_business' => $this->type_of_business,
        'other' => $this->other,
        'remark' => $this->remark,

        'documents' => [
            'cancelled_cheque' => [
                'file' => $this->cancelled_cheque_file,
                'file_id' => $this->cancelled_cheque_file_id,
            ],
            'vishwakarma_form_copy' => [
                'file' => $this->vishwakarma_form_copy_file,
                'file_id' => $this->vishwakarma_form_copy_file_id,
            ],
        ],

        'status' => $this->status,
        'type' => $this->type,
        'is_bulk' => $this->is_bulk,
        'category_name' => $this->category_name,
        'created_at' => optional($this->created_at)->toDateTimeString(),
        'updated_at' => optional($this->updated_at)->toDateTimeString(),

        //$this->authorization_document_path . '/' .  $this->authorization_document,
        ];
    }
}