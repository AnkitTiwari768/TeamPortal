<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AiCatalog;

use Illuminate\Support\Facades\DB;

final class AiCatalogRepository
{
   
    public function findLocationId(string $table, string $code): ?int
    {
        $id = DB::table($table)->where('code', $code)->value('id');
        return $id !== null ? (int) $id : null;
    }

    public function msmeSchemeExistsForUdyam(string $udyamNo): bool
    {
        return DB::table(AiCatalogConstants::MSME_SCHEMES_TABLE)->where('udyam_no', $udyamNo)->exists();
    }
    public function msmeSchemeDuplicateExists(string $udyamNo, string $mobile): bool
    {
        return DB::table(AiCatalogConstants::MSME_SCHEMES_TABLE)
            ->where('udyam_no', $udyamNo)
            ->orWhere('mobile', $mobile)
            ->exists();
    }

    public function findMsmeSchemeByMobile(string $mobile): ?object
    {
        return DB::table(AiCatalogConstants::MSME_SCHEMES_TABLE)
            ->where('mobile', $mobile)
            ->where('is_from_aicatalog', 1)
            ->first();
    }

    // Returns the users.id linked to the given team_msme_schemes row id, or null if not found.
    public function findUserIdBySchemeId(string $schemeId): ?string
    {
        $userId = DB::table(AiCatalogConstants::MSME_SCHEMES_TABLE)->where('id', $schemeId)->value('user_id');

        return $userId !== null ? (string) $userId : null;
    }

    public function insertMsmeScheme(array $data): void
    {
        DB::table(AiCatalogConstants::MSME_SCHEMES_TABLE)->insert($data);
    }
    public function insertUser(array $data): void
    {
        DB::table(AiCatalogConstants::USERS_TABLE)->insert($data);
    }

    // Inserts a new row into ai_catalog_api_logs recording one API request/response.
    public function insertApiLog(array $data): void
    {
        DB::table(AiCatalogConstants::API_LOGS_TABLE)->insert($data);
    }
}
