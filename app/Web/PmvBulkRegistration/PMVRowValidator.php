<?php

namespace App\Web\PmvBulkRegistration;

use Illuminate\Support\Facades\DB;

class PMVRowValidator
{
    public static function validate($data)
    {
        $errors = [];

        // Normalize inputs
        $ownerName = trim($data['owner_name'] ?? '');
        $storeName = trim($data['store_name'] ?? '');
        $mobile    = trim($data['mobile'] ?? '');
        $email     = trim($data['email'] ?? '');
        $pan       = strtoupper(trim($data['pan'] ?? ''));

        /* ================= OWNER NAME ================= */
   
        if (empty($ownerName)) {
            $errors[] = "Owner name is required";
        } elseif (!preg_match('/^[a-zA-Z. ]+$/', $ownerName)) {
            $errors[] = "Owner name may contain only letters, spaces, and dot (.)";
        }

        /* ================= STORE NAME ================= */
        if (empty($storeName)) {
            $errors[] = "Store name is required";
        }
        // ✅ No regex restriction → all characters allowed

        /* ================= MOBILE ================= */
        if (empty($mobile)) {
            $errors[] = "Mobile number is required";
        } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
            $errors[] = "Mobile must be 10 digits and start with 6-9";
        }

        /* ================= EMAIL ================= */
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email format is invalid";
            }
        }

        /* ================= PAN ================= */
        if (!empty($pan)) {
            if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
                $errors[] = "PAN format is invalid";
            }
        }

        /* ================= DUPLICATE CHECK ================= */
        $duplicateFields = [];

        if (!empty($email)) {
            if (DB::table('pm_registrations')->where('email', $email)->exists()) {
                $duplicateFields[] = "Email";
            }
        }

        if (!empty($pan)) {
            if (DB::table('pm_registrations')->where('pan_no', $pan)->exists()) {
                $duplicateFields[] = "PAN";
            }
        }

        if (!empty($mobile)) {
            if (DB::table('pm_registrations')->where('mobile', $mobile)->exists()) {
                $duplicateFields[] = "Mobile";
            }
        }

        if (!empty($duplicateFields)) {
            $errors[] = "Duplicate record found for " . implode(', ', $duplicateFields);
        }

        /* ================= FINAL RETURN ================= */

        if (empty($errors)) {
            return [];
        }

        // 👉 Single error → return as it is
        if (count($errors) === 1) {
            return [$errors[0]];
        }

        // 👉 Multiple errors → comma separated
        return [implode(', ', $errors)];
    }
}