<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\View\View;

class ProposedWorkshopController
{
    public function index(): View
    {
        $title = __('workshop.proposed_workshop');
        return view('workshop.index', compact('title'));
    }
}
