<?php

namespace App\Web\Notification;
use App\Web\Notification\Notification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    private function getRoleId()
    {
        return DB::table('user_roles')->where('user_id', auth()->id())->value('role_id');
    }

    // public function getNotifications()
    // {
    //     $roleId = $this->getRoleId();
    //     $userId = auth()->id();

    //     return Notification::with('template')

    //         ->where(function ($query) use ($roleId, $userId) {

    //             // ROLE WISE
    //             $query->where(function ($q) use ($roleId) {
    //                 $q->where('type', 2)
    //                 ->where('to_role', $roleId);
    //             });

    //             // USER WISE
    //             $query->orWhere(function ($q) use ($userId) {
    //                 $q->where('type', 1)
    //                 ->where('to_user_id', $userId);
    //             });

    //         })

    //         ->where('is_read', 0)
    //         ->where('status', 1)
    //         ->latest()
    //         ->get();
    // }

    // public function markAllAsRead()
    // {
    //     $roleId = $this->getRoleId();
    //     $userId = auth()->id();

    //     return Notification::where(function ($query) use ($roleId, $userId) {

    //             $query->where(function ($q) use ($roleId) {
    //                 $q->where('type', 2)
    //                 ->where('to_role', $roleId);
    //             });

    //             $query->orWhere(function ($q) use ($userId) {
    //                 $q->where('type', 1)
    //                 ->where('to_user_id', $userId);
    //             });

    //         })

    //         ->update([
    //             'is_read' => 1,
    //             'read_at' => now()
    //         ]);
    // }
    // public function markSingleRead($id)
    // {
    //     $roleId = $this->getRoleId();
    //     $userId = auth()->id();

    //     return Notification::where('id', $id)

    //         ->where(function ($query) use ($roleId, $userId) {

    //             $query->where(function ($q) use ($roleId) {
    //                 $q->where('type', 2)
    //                 ->where('to_role', $roleId);
    //             });

    //             $query->orWhere(function ($q) use ($userId) {
    //                 $q->where('type', 1)
    //                 ->where('to_user_id', $userId);
    //             });

    //         })

    //         ->update([
    //             'is_read' => 1,
    //             'read_at' => now()
    //         ]);
    // }

    public function getNotifications()
    {
        $roleId = $this->getRoleId();
        $userId = auth()->id();
        $parentUserId = auth()->user()->parent_user_id;

        return Notification::with('template')

            ->where(function ($query) use ($roleId, $userId, $parentUserId) {

                // ROLE WISE
                $query->where(function ($q) use ($roleId) {
                    $q->where('type', 2)
                    ->where('to_role', $roleId);
                });
          
                // USER WISE
                $query->orWhere(function ($q) use ($userId, $parentUserId) {

                    $q->where('type', 1)
                    ->where(function ($subQuery) use ($userId, $parentUserId) {

                        $subQuery->where('to_user_id', $userId);

                        if ($parentUserId) {
                            $subQuery->orWhere('to_user_id', $parentUserId);
                        }
                    });
                });

            })

            ->where('is_read', 0)
            ->where('status', 1)
            ->latest()
            ->get();
    }

    public function markAllAsRead()
    {
        $roleId = $this->getRoleId();
        $userId = auth()->id();
        $parentUserId = auth()->user()->parent_user_id;

        return Notification::where(function ($query) use ($roleId, $userId, $parentUserId) {

                $query->where(function ($q) use ($roleId) {
                    $q->where('type', 2)
                    ->where('to_role', $roleId);
                });

                $query->orWhere(function ($q) use ($userId, $parentUserId) {

                    $q->where('type', 1)
                    ->where(function ($subQuery) use ($userId, $parentUserId) {

                        $subQuery->where('to_user_id', $userId);

                        if ($parentUserId) {
                            $subQuery->orWhere('to_user_id', $parentUserId);
                        }
                    });
                });

            })

            ->update([
                'is_read' => 1,
                'read_at' => now()
            ]);
    }
    public function markSingleRead($id)
    {
        $roleId = $this->getRoleId();
        $userId = auth()->id();
        $parentUserId = auth()->user()->parent_user_id;

        return Notification::where('id', $id)

            ->where(function ($query) use ($roleId, $userId, $parentUserId) {

                $query->where(function ($q) use ($roleId) {
                    $q->where('type', 2)
                    ->where('to_role', $roleId);
                });

                $query->orWhere(function ($q) use ($userId, $parentUserId) {

                    $q->where('type', 1)
                    ->where(function ($subQuery) use ($userId, $parentUserId) {

                        $subQuery->where('to_user_id', $userId);

                        if ($parentUserId) {
                            $subQuery->orWhere('to_user_id', $parentUserId);
                        }
                    });
                });

            })

            ->update([
                'is_read' => 1,
                'read_at' => now()
            ]);
    }
}