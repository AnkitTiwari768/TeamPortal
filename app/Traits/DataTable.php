<?php

namespace App\Traits;

use App\Enums\PaymentStatus;
use App\Enums\ReviewStatus;

trait DataTable
{
    public function getDataTableParams(?string $prefix = null, ?array $columns = null)
    {
        $columns = $this->columns ?? $columns;
        // $this->columns = $columns ? $columns : $this->columns;

        $request = request()->all();

        $limit =  isset($request['length']) ? (int) $request['length'] : 10;

        $order = (
            isset($request['order']) &&
            isset($request['order'][0]) &&
            isset($request['order'][0]['column']) &&
            isset($columns[$request['order'][0]['column']])
        )
            ? $columns[$request['order'][0]['column']]
            : ($prefix ? "{$prefix}.id" : 'id');

        $dir = (
            isset($request['order']) &&
            isset($request['order'][0]) &&
            isset($request['order'][0]['dir'])
        )
            ? $request['order'][0]['dir']
            : 'DESC';

        $search = (
            isset($request['search']) &&
            isset($request['search']['value']) &&
            !empty($request['search']['value'])
        )
            ? $request['search']['value']
            : null;

        $page = (isset($request['page']) && !empty($request['page']));

        $filters = (isset($request['filters']) && !empty($request['filters']))
            ? $request['filters']
            : null;

        return [$limit, $order, $dir, $search, $page, $filters, $request['start'] ?? 0];
    }

    public function getDataTableResult($result)
    {
        $draw = request()->input('draw');

        return [
            "draw"            => intval($draw),
            "recordsTotal"    => $result->total(),
            "recordsFiltered" => $result->total(),
            "data"            => $result,
            'current_page'    => $result->currentPage(),
            'next'            => $result->nextPageUrl(),
            'previous'        => $result->previousPageUrl(),
            'per_page'        => $result->perPage(),
        ];
    }

    protected function dataTablePaymentStatus(string $field)
    {
        return '(
            CASE
                WHEN ' . $field . ' =' . PaymentStatus::Success->value . '
                THEN "' . __('Success') . '"
                WHEN ' . $field . ' =' . PaymentStatus::Failed->value . '
                THEN "' . __('Failed') . '"
                WHEN ' . $field . ' =' . PaymentStatus::Declined->value . '
                THEN "' . __('Declined') . '"
                WHEN ' . $field . ' =' . PaymentStatus::Initiated->value . '
                THEN "' . __('Initiated') . '"
                WHEN ' . $field . ' =' . PaymentStatus::Refunded->value . '
                THEN "' . __('Refunded') . '"
                ELSE "' . __('message.pending') . '"
            END 
        )';
    }

    protected function dataTableReviewStatus(string $field)
    {

        return '(
            CASE
                WHEN ' . $field . ' =' . ReviewStatus::Approve->value . '
                THEN "' . __('message.approve') . '"
                WHEN ' . $field . ' =' . ReviewStatus::Revert->value . '
                THEN "' . __('message.revert') . '"
                WHEN ' . $field . ' =' . ReviewStatus::Reject->value . '
                THEN "' . __('message.reject') . '"
                WHEN ' . $field . ' =' . ReviewStatus::Pending->value . '
                THEN "' . __('message.pending') . '"
                WHEN ' . $field . ' =' . ReviewStatus::Submitted->value . '
                THEN "' . __('message.submitted') . '"
                WHEN ' . $field . ' =' . ReviewStatus::InProgress->value . '
                THEN "' . __('message.in_progress') . '"
                ELSE "' . __('message.draft') . '"
            END 
        )';
    }

    protected function dataTableAppDate(string $field)
    {
        return 'DATE_FORMAT(' . $field . ', "' . config('constant.mysql_app_date') . '")';
    }

    protected function datatable_status($field = 'status')
    {
        return '(
            CASE 
                WHEN ' . $field . ' =' . config('constant.ACTIVE') . '
                THEN "Active"
                ELSE "In-active"
            END
        )';
    }



    protected function datatable_mysql_datetime_hm($field)
    {
        return 'DATE_FORMAT(' . $field . ', "' . config('constant.mysql_datetime_hm') . '")';
    }

    protected function datatable_mysql_datetime_hms($field)
    {
        return 'DATE_FORMAT(' . $field . ', "' . config('constant.app_datetime_hms') . '")';
    }

    protected function datatable_mysql_date($field)
    {
        return 'DATE_FORMAT(' . $field . ', "' . config('constant.mysql_date') . '")';
    }


    protected function escape_special_characters($str)
    {
        if (! empty($str)) {
            return (! preg_match('/[#$%^&*+=\[\]\';\/{}|"<>?~\\\\]/', $str)) ? true : false;
        }
    }
}
