<?php

namespace App\Web\RootManager\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ClientController;
use App\Web\RootManager\Common\UserByPassService;

class UserByPassController extends ClientController
{
    public function index()
    {
        $title = "User By Pass";

        $users = DB::table('users')
            ->select('id', 'email', 'username', 'is_bypass')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->orderBy('email', 'ASC')
            ->get();

        return view(
            'root-manager.common.user_by_pass',
            compact('title', 'users')
        );
    }

    public function update(
        Request $request,
        UserByPassService $service
    ) {

        $request->validate([
            'email'   => 'required|email',
            'by_pass' => 'required|in:0,1',
        ]);

        $result = $service->updateByPass(
            $request->email,
            (int) $request->by_pass
        );

        return response()->json([
            'status'  => $result['success'],
            'message' => $result['message']
        ]);
    }
}