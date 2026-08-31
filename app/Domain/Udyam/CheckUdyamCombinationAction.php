<?php

declare(strict_types=1);

namespace App\Domain\Udyam;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckUdyamCombinationAction
{
    private const API_BASE_URL = 'https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam';
    private const AUTH_KEY = 'urteam-bnRlYW1AbTd1MUs=';

    /**
     * Check if a single Udyam number and mobile number combination exists.
     *
     * @param string $udyamNo
     * @param string $mobile
     * @return array
     */
    public function check(string $udyamNo, string $mobile): array
    {
        $url = sprintf('%s/%s,%s,%s', self::API_BASE_URL, trim($udyamNo), trim($mobile), self::AUTH_KEY);

        try {
            // Using timeout and withoutVerifying to handle potential SSL issues on government portals
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->get($url);

            if (!$response->successful()) {
                return [
                    'exists' => false,
                    'error' => 'API HTTP error: ' . $response->status(),
                ];
            }

            $xml = $response->body();
            if (empty($xml)) {
                return [
                    'exists' => false,
                    'error' => 'Empty response from API',
                ];
            }

            // Suppress errors and load XML
            $xmlObject = @simplexml_load_string($xml);
            if ($xmlObject === false) {
                return [
                    'exists' => false,
                    'error' => 'Failed to parse XML response from API',
                ];
            }

            $basicDetail = $xmlObject->BasicDetail ?? null;
            if (!$basicDetail) {
                return [
                    'exists' => false,
                    'error' => 'BasicDetail missing from API response',
                ];
            }

            $resUdyamNo = (string) ($basicDetail->UdyamNo ?? '');
            $error = (string) ($basicDetail->Error ?? '');

            if (strtoupper($resUdyamNo) === 'NO' || !empty($error)) {
                return [
                    'exists' => false,
                    'error' => !empty($error) ? $error : 'Wrong Details / Combination does not exist',
                ];
            }

            return [
                'exists' => true,
                'udyam_no' => $resUdyamNo,
                'enterprise_name' => (string) ($basicDetail->EnterpriseName ?? ''),
                'entrepreneur_name' => (string) ($basicDetail->EntrepreneurName ?? ''),
                'email' => (string) ($basicDetail->EmailId ?? ''),
                'mobile' => $mobile,
                'major_activity' => (string) ($basicDetail->MajorActivity ?? ''),
                'enterprise_type' => (string) ($basicDetail->EnterpriseType ?? ''),
            ];
        } catch (\Exception $e) {
            Log::error('Udyam API verification failed: ' . $e->getMessage(), [
                'udyam_no' => $udyamNo,
                'mobile' => $mobile,
                'exception' => $e
            ]);

            return [
                'exists' => false,
                'error' => 'Exception occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check multiple Udyam number and mobile number combinations.
     *
     * @param array<string, string> $udyamMobileMap Key as Udyam Number, Value as Mobile Number
     * @return array<string, array>
     */
    public function checkMultiple(array $udyamMobileMap): array
    {
        $results = [];

        foreach ($udyamMobileMap as $udyamNo => $mobile) {
            $results[$udyamNo] = $this->check((string) $udyamNo, (string) $mobile);
        }

        return $results;
    }
}
