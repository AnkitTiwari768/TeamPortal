<?php

namespace App\Traits;

trait Respond 
{
    public function success($data = [], $message = null)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function error($errors = [], $message = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ],422);
    }

    public function handleException(\Throwable $e)
    {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'errors' => $e->getTrace()
        ]);
    }

    public function respondExcelValidationErrors(\Maatwebsite\Excel\Validators\ValidationException $e)
    {
        $failures = $e->failures();
        $errors = [];

        foreach ($failures as $failure) 
        {
            $errors[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values()
            ];
        }
        
        return $this->error($errors);
    }

    public function created($data = [], $message = null)
    {
        return $this->success($data, $message ?? __('message.created'));
    }
    
    public function updated($data = [])
    {
        return $this->success($data, __('message.updated'));    
    }

    public function deleted()
    {
        return $this->success([], __('message.deleted'));
    }

    public function notCreated($errors)
    {
        return $this->error($errors, __('message.not_created'));
    }

    public function notUpdated($errors = [])
    {
        return $this->error($errors, __('message.not_updated'));
    }

    public function notDeleted($errors = [])
    {
        return $this->error($errors, __('message.not_deleted'));
    }

    public function handler($exception)
    {
        return $this->error($exception, $exception->getMessage());
    }
}
