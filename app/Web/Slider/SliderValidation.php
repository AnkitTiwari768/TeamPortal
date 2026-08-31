<?php

declare(strict_types=1);

namespace App\Web\Slider;
use Illuminate\Validation\Rule;

class SliderValidation
{
    public static function getRules(array $request = [], ?string $id = null): array
    {
        $urlRequired = '';
        $imageRequired = '';

        // If slider type is 2 (maybe external link), URL becomes required
        if (!empty($request['type']) && $request['type'] == 2) {
            $urlRequired = 'required';
        }

        // On create (no ID), image is required
        if (empty($id)) {
            $imageRequired = 'required';
        }

        return [
            'title_en' => [
                'bail',
                'required',
                'min:2',
                'max:150',
                'string',
                'unique:cms_menus,title_en,' . $id,
            ],
            'title_mr' => [
                'bail',
                'required',
                'min:2',
                'max:150',
                'string',
                'unique:cms_menus,title_mr,' . $id,
            ],
            'sort_order' => [
                'bail',
                'required',
                'integer',
            ],
            'type' => [
                'bail',
                'required',
                'integer',
            ],
            'url' => array_filter([
                'bail',
                'nullable',
                'url',
            ]),
            'images' => array_filter([
                'bail',
                $imageRequired,
            ]),
            'is_active' => [
                'boolean',
            ],
        ];
    }

    public static function CustomMessages(): array
    {
        return [
            'title_en.required' => 'The Title In English field is required.',
            'title_en.min' => 'The Title In English must be at least 2 characters.',
            'title_en.max' => 'The Title In English must be at most 150 characters.',
            'title_en.unique' => 'The Title In English has already been taken.',

            'title_hi.required' => 'The Title In Marathi field is required.',
            'title_hi.min' => 'The Title In Marathi must be at least 2 characters.',
            'title_hi.max' => 'The Title In Marathi must be at most 150 characters.',
            'title_hi.unique' => 'The Title In Marathi has already been taken.',

            'url.required' => 'The URL field is required when type is External.',
            'url.url' => 'The URL must be a valid format.',

            'images.required' => 'The image is required for new sliders.',
        ];
    }
}
