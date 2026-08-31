<?php

declare(strict_types=1);

namespace App\Web\Ia;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;

class IaMseController extends ClientController
{
    //private static string $module = 'ia.index';

    public function __construct(private IaMseService $service) {}


    public function registeredMsme(): View
    {
        return view('ia.registered-msme')
            ->with('title', 'Registered MSE')
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function getRegisteredMsmeList()
    {
        return $this->success($this->service->getRegisteredMsmeList());
    }

}
