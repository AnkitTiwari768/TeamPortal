<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ListPendingIA
{
    use DataTable;

    public function execute(?IAStatus $status = null)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('industrial_associations as ia')
            ->join('attribute_values as av', 'ia.entity_type', '=', 'av.id')
            ->leftJoin('file_uploads as fu', 'ia.authorization_document_id', '=', 'fu.id')
            ->select(
                'ia.*',
                'av.attribute_value as entity_type',
                'fu.file_path as authorization_document_path'

            )
            ->where('ia.status', $status ? $status->value : IAStatus::PENDING->value);

        if (!empty($filters['from_date'])) {
            $from = Carbon::createFromFormat('d-m-Y', $filters['from_date'])->format('Y-m-d');
        }

        if (!empty($filters['to_date'])) {
            $to = Carbon::createFromFormat('d-m-Y', $filters['to_date'])->format('Y-m-d');
        }

        if (!empty($from) && !empty($to)) {
            $query->whereBetween('ms.created_at', [$from, $to]);
        } elseif (!empty($from)) {
            $query->whereDate('ms.created_at', '>=', $from);
        } elseif (!empty($to)) {
            $query->whereDate('ms.created_at', '<=', $to);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('ia.organization_name', 'like', "%{$search}%")
                    ->orWhere('av.attribute_value', 'like', "%{$search}%")
                    ->orWhere('ia.entity_email', 'like', "%{$search}%")
                    ->orWhere('ia.contact_number', 'like', "%{$search}%")
                    ->orWhere('ia.registration_number', 'like', "%{$search}%")
                    ->orWhere('ia.website', 'like', "%{$search}%")
                    ->orWhere('ia.contact_person_name', 'like', "%{$search}%")
                    ->orWhere('ia.contact_person_phone', 'like', "%{$search}%")
                    ->orWhere('ia.contact_person_email', 'like', "%{$search}%")
                    ->orWhere('ia.created_at', 'like', "%{$search}%");
            });
        }

        if ($order === 'id') {
            $query->orderBy('ia.updated_at', 'desc');
        } else {
            $query->orderBy($order, $dir);
        }

        if ($page) {
            return $this->getDataTableResult(
                ListIAResource::collection($query->paginate($limit))
            );
        }

        return ListIAResource::collection($query->get());
    }
}
