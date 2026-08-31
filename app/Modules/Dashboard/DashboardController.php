<?php 

declare(strict_types=1);

namespace App\Modules\Dashboard;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;

final class DashboardController extends ClientController 
{
    public function index()
    {
        return view('dashboard.index')
            ->with('title', __('message.dashboard_list'));
    }
}