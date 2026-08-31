<?php

declare(strict_types=1);

namespace App\Web\Attributes;

use App\Core\BaseController;

class AttributeController extends BaseController
{
    public function index()
    {
        return view('attribute.index');
    }

    public function list() {}

    public function create() {}
}
