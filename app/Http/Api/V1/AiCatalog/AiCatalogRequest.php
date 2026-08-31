<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AiCatalog;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class AiCatalogRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    // Returns the validation rule set for the currently resolved controller action.
    public function rules(): array
    {
        return match ($this->route()?->getActionMethod()) {
            'registration' => [
                'mobile_number' => ['required', 'regex:'.AiCatalogConstants::MOBILE_NUMBER_REGEX],
                'udyam_registration_number' => ['required', 'regex:'.AiCatalogConstants::UDYAM_NUMBER_REGEX],
            ],
            'login' => [
                'mobile_number' => ['required', 'regex:'.AiCatalogConstants::MOBILE_NUMBER_REGEX],
                'udyam_registration_number' => ['required', 'regex:'.AiCatalogConstants::UDYAM_NUMBER_REGEX],
                'client_id' => ['nullable', 'string'],
            ],
            'udyamDetails' => [
                'mobile_number' => ['required', 'regex:'.AiCatalogConstants::MOBILE_NUMBER_REGEX],
                'udyam_registration_number' => ['required', 'regex:'.AiCatalogConstants::UDYAM_NUMBER_REGEX],
            ],
            default => [],
        };
    }

    // Overrides Laravel's default validation-failure handling to return a fixed JSON shape.
    protected function failedValidation(ValidatorContract $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors()->toArray(),
        ], 422));
    }

    // Overrides Laravel's default authorization-failure handling to return a fixed JSON shape.
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'You do not have permission to access this resource.',
        ], 403));
    }
}
