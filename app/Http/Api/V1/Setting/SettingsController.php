<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Setting;

use App\Http\Controllers\ApiController;
use App\Http\Api\V1\Setting\SettingValidation as Validation;
use Illuminate\Http\Request;

class SettingsController extends ApiController 
{
    public function __construct(private SettingService $service) 
    {
        $this->service = $service;
    }

    public function index()
    {
        try 
        {
            $result = $this->service->getSettings();
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function store(Request $request) 
    {
       try 
        {
            $validator = Validation::getRules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

            $result = $this->service->create($validator->validated());
            return $this->created($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $id) 
    {
        try 
        {
            $validator = Validation::getRules($request->all(), $id);
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

            $this->service->update($validator->validated(), $id);
            return $this->updated();
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }
}