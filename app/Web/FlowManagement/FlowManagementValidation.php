<?php 

declare(strict_types=1);

namespace App\Web\FlowManagement;

use Illuminate\Validation\Rule;

class FlowManagementValidation 
{
    public static function getRules(array $request, ?string $id = null): array
    {
        $requiredFMenu = '';

        if (!empty($request['show_in'])) {
            $showIn = $request['show_in'];

            if (in_array(1, $showIn) && in_array(2, $showIn)) {  
                $requiredFMenu = 'required';
            } elseif (in_array(2, $showIn)) {   
                $requiredFMenu = 'required';
            }
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
            'sub_menu' => [
                'bail',
                'required',
                'integer',
            ],
            'sort_order' => [
                'bail',
                'required',
                'integer',
            ],
            'template' => [
                'bail',
                'required',
                'integer',
            ],
            'show_in' => [
                'bail',
                'required',
                'array',
            ],
            'show_in.*' => [
                'bail',
                'required',
                'string',
            ],
            'fmenu_category' => array_filter([
                'bail',
                $requiredFMenu,
                //'string',
            ]),
            'is_active' => [
                'boolean',
            ]
        ];
    }

    public static function CustomMessages(): array
    {
        return [
            'title_en.required' => 'The Title In English field is required.',
            'title_en.min' => 'The Title In English must be at least 2 characters.',
            'title_en.max' => 'The Title In English must be at max 150 characters.',
            'title_mr.required' => 'The Title In Marathi field is required.',
            'title_mr.min' => 'The Title In Marathi must be at least 2 characters.',
            'title_mr.max' => 'The Title In Marathi must be at max 150 characters.',
            'fmenu_category.required' => 'The Footer Menu Category field is required.',
        ];
    }
}
