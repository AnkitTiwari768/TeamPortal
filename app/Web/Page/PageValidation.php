<?php

declare(strict_types=1);

namespace App\Web\Page;

use Illuminate\Validation\Rule;

class PageValidation
{
    public static function getRules(array $request = [], ?string $id = null): array
    {
        return [
            'menu_id' => [
                'bail',
                'required',
                'string',
                'unique:cms_pages,menu_id,' . $id,
            ],
            'title_en' => [
                'bail',
                'required',
                'min:2',
                'max:150',
                'string',
                'unique:cms_pages,title_en,' . $id,
            ],
            'title_mr' => [
                'bail',
                'required',
                'min:2',
                'max:150',
                'string',
                'unique:cms_pages,title_mr,' . $id,
            ],
            'description_en' => [
                'bail',
                'required',
                'string',
            ],
            'description_mr' => [
                'bail',
                'required',
                'string',
            ],
            'is_active' => [
                'boolean',
            ]
        ];
    }

    public static function CustomMessages(): array
    {
        return [
            'menu_id.required' => 'The Menu Title field is required.',
            'menu_id.unique' => 'The Menu Title has already been taken.',

            'title_en.required' => 'The Title In English field is required.',
            'title_en.min' => 'The Title In English must be at least 2 characters.',
            'title_en.max' => 'The Title In English must be at most 150 characters.',
            'title_en.unique' => 'The Title In English has already been taken.',

            'title_mr.required' => 'The Title In Marathi field is required.',
            'title_mr.min' => 'The Title In Marathi must be at least 2 characters.',
            'title_mr.max' => 'The Title In Marathi must be at most 150 characters.',
            'title_mr.unique' => 'The Title In Marathi has already been taken.',

            'description_en.required' => 'The Description In English field is required.',
            'description_mr.required' => 'The Description In Marathi field is required.',
        ];
    }
}
