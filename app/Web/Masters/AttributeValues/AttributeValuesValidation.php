<?php

declare(strict_types=1);

namespace App\Web\Masters\AttributeValues;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttributeValuesValidation
{
    public static function getRules($request, ?string $id = null, ?string $attributeId = null)
    {
        return Validator::make(
            $request,
            [
                'attribute_id'    => ['required', 'exists:attributes,id'],
                'parent_id'       => ['nullable', 'exists:attribute_values,id'],
                'sort_order'      => ['nullable', 'integer'],
                'attribute_value' => ['required', 'string', 'max:255'],
                'status'          => ['required', 'integer', 'in:0,1'],
            ]
        );
    }
}