<?php

declare(strict_types=1);

namespace App\Web\Import;

trait Importable
{
    // Static arrays to track combinations within the Excel file
    private static array $teamProviderCombinations = [];
    private static array $teamCatalogueReportCombinations = [];
    private static array $teamCredentialReportCombinations = [];
    private static array $teamCatalogueUrlCombinations = [];
    private static array $teamCredentialUrlCombinations = [];
    protected function SkipEmptyRow($row)
    {
        return $row->filter(function ($cell) {
            return !is_null($cell) && trim((string) $cell) !== '';
        })->isEmpty();
    }

    protected function validateExcelHeader($headerRow)
    {
        $headers = array_map(function ($value) {
            return strtolower(trim((string) $value));
        }, $headerRow);

        $headerCount = count($headers);
        $expectedCount = count($this->requiredHeaders);

        while ($headerCount > $expectedCount && $headers[$headerCount - 1] === '') {
            array_pop($headers);
            $headerCount--;
        }

        $missing = array_diff($this->requiredHeaders, $headers);
        $extra = array_diff($headers, $this->requiredHeaders);

        if (!empty($missing) || !empty($extra)) {
            \Log::error('Header validation failed', [
                'missing' => $missing,
                'extra' => $extra,
                'expected' => $this->requiredHeaders,
                'actual' => $headers
            ]);

            throw new \Exception(
                "Invalid Excel format. Please upload file using the provided template."
            );
        }
    }

    protected function clean($value)
    {
        $value = is_string($value) ? trim($value) : $value;
        $value = $value === '' ? null : $value;
        return $this->cleanExcelValue($value);
    }

    protected function cleanExcelValue($value)
    {
        if (is_string($value)) {
            // First replace newlines with spaces, then collapse multiple spaces
            $value = preg_replace('/\r|\n/', ' ', $value);
            $value = preg_replace('/\s+/', ' ', $value);
            return trim($value);
        }
        return $value;
    }
    /**
     * Check for duplicate team_id + provider_id combination within Excel
     */
    protected function checkDuplicateTeamProviderInExcel($teamId, $providerId)
    {
        return function ($attribute, $value, $fail) use ($teamId, $providerId) {
            $combination = $teamId . '|' . $providerId;

            if (in_array($combination, self::$teamProviderCombinations)) {
                $fail("The combination of Team ID '{$teamId}' and Provider ID '{$providerId}' is duplicated in the Excel file.");
                return;
            }

            self::$teamProviderCombinations[] = $combination;
        };
    }

    /**
     * Check for duplicate team_id + catalogue_score_report_id combination within Excel
     */
    protected function checkDuplicateTeamCatalogueReportInExcel($teamId, $catalogueReportId)
    {
        return function ($attribute, $value, $fail) use ($teamId, $catalogueReportId) {
            $combination = $teamId . '|' . $catalogueReportId;

            if (in_array($combination, self::$teamCatalogueReportCombinations)) {
                $fail("The combination of Team ID '{$teamId}' and Catalogue Score Report ID '{$catalogueReportId}' is duplicated in the Excel file.");
                return;
            }

            self::$teamCatalogueReportCombinations[] = $combination;
        };
    }

    /**
     * Check for duplicate team_id + credential_report_id combination within Excel
     */
    protected function checkDuplicateTeamCredentialReportInExcel($teamId, $credentialReportId)
    {
        return function ($attribute, $value, $fail) use ($teamId, $credentialReportId) {
            $combination = $teamId . '|' . $credentialReportId;

            if (in_array($combination, self::$teamCredentialReportCombinations)) {
                $fail("The combination of Team ID '{$teamId}' and Credential Report ID '{$credentialReportId}' is duplicated in the Excel file.");
                return;
            }

            self::$teamCredentialReportCombinations[] = $combination;
        };
    }

    /**
     * Check for duplicate team_id + catalogue_score_url combination within Excel
     */
    protected function checkDuplicateTeamCatalogueUrlInExcel($teamId, $catalogueUrl)
    {
        return function ($attribute, $value, $fail) use ($teamId, $catalogueUrl) {
            $combination = $teamId . '|' . $catalogueUrl;

            if (in_array($combination, self::$teamCatalogueUrlCombinations)) {
                $fail("The combination of Team ID '{$teamId}' and Catalogue Score URL '{$catalogueUrl}' is duplicated in the Excel file.");
                return;
            }

            self::$teamCatalogueUrlCombinations[] = $combination;
        };
    }

    /**
     * Check for duplicate team_id + credential_score_url combination within Excel
     */
    protected function checkDuplicateTeamCredentialUrlInExcel($teamId, $credentialUrl)
    {
        return function ($attribute, $value, $fail) use ($teamId, $credentialUrl) {
            $combination = $teamId . '|' . $credentialUrl;

            if (in_array($combination, self::$teamCredentialUrlCombinations)) {
                $fail("The combination of Team ID '{$teamId}' and Credential Score URL '{$credentialUrl}' is duplicated in the Excel file.");
                return;
            }

            self::$teamCredentialUrlCombinations[] = $combination;
        };
    }

    /**
     * Combined validator for all Excel duplicate combinations
     */
    protected function checkAllExcelDuplicates($teamId, $providerId, $catalogueReportId, $credentialReportId, $catalogueUrl, $credentialUrl)
    {
        return [
            $this->checkDuplicateTeamProviderInExcel($teamId, $providerId),
            $this->checkDuplicateTeamCatalogueReportInExcel($teamId, $catalogueReportId),
            $this->checkDuplicateTeamCredentialReportInExcel($teamId, $credentialReportId),
            $this->checkDuplicateTeamCatalogueUrlInExcel($teamId, $catalogueUrl),
            $this->checkDuplicateTeamCredentialUrlInExcel($teamId, $credentialUrl),
        ];
    }

    /**
     * Reset all combination trackers (useful for testing or multiple imports)
     */
    protected function resetExcelCombinationTrackers()
    {
        self::$teamProviderCombinations = [];
        self::$teamCatalogueReportCombinations = [];
        self::$teamCredentialReportCombinations = [];
        self::$teamCatalogueUrlCombinations = [];
        self::$teamCredentialUrlCombinations = [];
    }
}
