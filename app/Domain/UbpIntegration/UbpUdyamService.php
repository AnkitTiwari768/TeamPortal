<?php

namespace App\Domain\UbpIntegration;

use Illuminate\Support\Facades\Http;

class UbpUdyamService
{
   /* public function fetchDetails(string $udyamNo, string $mobile): ?array
    {
        $url = "https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyamNo,$mobile,b2bmrt-VGVzdEBoeXc2MA==";

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                $xml = $response->body();
                if (empty($xml)) {
                    return null;
                }

                $xmlObject = simplexml_load_string($xml);
                if ($xmlObject === false) {
                    return null;
                }

                $json = json_encode($xmlObject);
                $data = json_decode($json, true);
                return $data;
            }

            return null;

        }

        catch (\Exception $e) {
            return null;
        }
    }*/

    public function fetchDetails($udyam_no, $mobile)
	{
		$response = Http::get("https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyam_no,$mobile,b2bmrt-VGVzdEBoeXc2MA==");
        // dd($response);
		if ($response->successful()) {
			$xml = $response->body();
			$xmlObject = simplexml_load_string($xml);
			$json = json_encode($xmlObject);
			//echo "<pre/>";print_r(json_decode($json, true));exit;
			return json_decode($json, true);
		} else {
			return response()->json([
				'error' => 'Failed to retrieve data',
				'status' => $response->status()
			]);
		}
	}
}
