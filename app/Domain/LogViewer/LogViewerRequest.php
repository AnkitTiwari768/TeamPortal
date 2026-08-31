<?php

declare(strict_types=1);

namespace App\Domain\LogViewer;

use Illuminate\Foundation\Http\FormRequest;

class LogViewerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'file' => 'nullable|string|max:255',
            'search' => 'nullable|string|max:500',
            'level' => 'nullable|string|in:ALL,all,EMERGENCY,ALERT,CRITICAL,ERROR,WARNING,NOTICE,INFO,DEBUG,ONLY_ERRORS,only_errors',
            'from_date' => 'nullable|string|max:20',
            'to_date' => 'nullable|string|max:20',
            'from_time' => 'nullable|string|max:10',
            'to_time' => 'nullable|string|max:10',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|in:25,50,100,250',
        ];
    }
}
