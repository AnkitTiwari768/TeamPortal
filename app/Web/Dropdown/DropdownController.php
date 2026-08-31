<?php

declare(strict_types=1);

namespace App\Web\Dropdown;

use App\Traits\HasAttribute;
use App\Traits\Respond;

class DropdownController
{
    use HasAttribute, Respond;

    public function getOptions()
    {
        $code = urldecode((string) request()->query('code'));
        $dependency = (bool) request()->query('dependency');

        $options = $this->listOf(code: $code, dependency: $dependency);

        $response = [];

        if ($options) {
            foreach ($options as $key => $value) {
                $response[] = ['id' => $key, 'text' => $value];
            }
        }

        return $this->success($response);
    }
}
