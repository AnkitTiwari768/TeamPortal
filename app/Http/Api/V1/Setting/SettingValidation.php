<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Setting;

use Illuminate\Support\Facades\Validator;

class SettingValidation
{
	public static function getRules($request, ?string $id = null)
    {
		return Validator::make($request, [
			'key' => ['required'],
			'value' => ['required']
		]);
	}
}