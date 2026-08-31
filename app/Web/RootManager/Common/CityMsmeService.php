<?php

namespace App\Web\RootManager\Common;

use Illuminate\Support\Facades\DB;

class CityMsmeService
{
    /**
     * Base cities list
     */
    private function getBaseCities()
    {
        return [
            'AHMADABAD', 'BENGALURU RURAL', 'BENGALURU URBAN', 'Chennai', 
            'GURUGRAM', 'Hyderabad', 'Kolkata', 'Mumbai', 'New Delhi', 'Pune'
        ];
    }

    /**
     * Get cities CTE for SQL query
     */
    private function getCitiesCTE()
    {
        $cities = $this->getBaseCities();
        $unionAll = [];
        
        foreach ($cities as $city) {
            $unionAll[] = "SELECT '{$city}' AS city";
        }
        
        return implode(" UNION ALL ", $unionAll);
    }

    /**
     * Get female MSME data
     */
    public function getCityMsmeFemaleData()
    {
        $citiesCTE = $this->getCitiesCTE();
        
        return DB::select("
            SELECT 
                c.city AS tier_1_city,
                COALESCE(COUNT(t.id), 0) AS female_mse_registrations
            FROM (
                {$citiesCTE}
            ) c
            LEFT JOIN (
                SELECT DISTINCT id, TRIM(LOWER(name)) AS name
                FROM locations
            ) l ON l.name = TRIM(LOWER(c.city))
            LEFT JOIN team_msme_schemes t ON t.district_id = l.id 
                AND LOWER(t.gender) = 'female'
            GROUP BY c.city
            ORDER BY female_mse_registrations DESC
        ");
    }

    /**
     * Get total MSME data
     */
    public function getTotalMsmeData()
    {
        $citiesCTE = $this->getCitiesCTE();
        
        return DB::select("
            SELECT 
                c.city AS tier_1_city,
                COALESCE(COUNT(t.id), 0) AS total_mse_registrations
            FROM (
                {$citiesCTE}
            ) c
            LEFT JOIN (
                SELECT DISTINCT id, TRIM(LOWER(name)) AS name
                FROM locations
            ) l ON l.name = TRIM(LOWER(c.city))
            LEFT JOIN team_msme_schemes t ON t.district_id = l.id
            GROUP BY c.city
            ORDER BY total_mse_registrations DESC
        ");
    }
}