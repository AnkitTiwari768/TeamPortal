<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use Illuminate\View\View;
use App\Http\Controllers\ClientController;

class CategoryWiseCountController extends ClientController
{
    public function __construct(
        private CategoryWiseCountService $service
    ) {
    }

    public function index(): View
    {
        return view('msme_all_list.categoryWiseCount.index')
            ->with('title', 'Category Wise MSME Count')
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function getCategoryWiseCountList()
    {
        return $this->success($this->service->getCategoryWiseCountList());
    }
}
