<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use Illuminate\Validation\Rule;

class RoleTypeRequest
{
    /**
     * The UI only posts `name` (plus the optional permission ids). `slug` is generated in
     * the backend and merged into the payload by the controller before validation runs, so
     * both unique constraints on role_types are covered by validation instead of surfacing
     * as a raw SQL duplicate-key error.
     */
    public static function getRules(?string $id = null): array
{
    return [
        'name' => array_filter([
            'bail',
            'required',
            'string',
            'min:2',
            'max:100',

            // Create par validation
            $id === null ? 'regex:/^[a-zA-Z0-9\s]*$/' : null,

            Rule::unique('role_types', 'name')->ignore($id)
        ]),

        'slug' => [
            'bail',
            'nullable',
            'string',
            'max:100',
            Rule::unique('role_types', 'slug')->ignore($id)
        ],

        'status' => [
            'nullable',
            'integer',
            Rule::in([
                config('constant.ACTIVE'),
                config('constant.INACTIVE')
            ])
        ],

        'permissions' => [
            'nullable',
            'array'
        ],

        'permissions.*' => [
            'bail',
            'required',
            'string',
            'distinct',
            'exists:permissions,id'
        ]
    ];
}

    public static function getMessages(): array
    {
        return [
            'name.required' => __('message.role_type_name_required'),
            'name.regex' => __('message.role_type_name_invalid'),
            'name.unique' => __('message.role_type_already_exists'),
            'slug.unique' => __('message.role_type_slug_already_exists'),
            'permissions.*.exists' => __('message.invalid_permission_selected')
        ];
    }
}
