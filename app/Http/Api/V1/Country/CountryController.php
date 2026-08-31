<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\Country;
use App\Http\Controllers\ApiController;
use App\Http\Api\V1\Country\CountryValidation as Validation;
use Illuminate\Http\Request;

class CountryController extends ApiController 
{
    public function __construct(private CountryService $service) 
    {
        $this->service = $service;
    }

    public function index()
    {
        try 
        {
            $result = $this->service->getCountries();
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

            $response = $this->service->create($validator->validated());
            return $this->created($response);
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
        catch (Throwable $exception) 
        {
            return $this->handleException($e);
        }
    }

    public function findById(string $id)
    {
        try 
        {
            $response = $this->service->findById($id);
            return $this->success($response);
        }
        catch (\Throwable $e)
        {
            return $this->handleException($e);
        }
    }
	
	public function getDetails()
    {
        try 
        {
            $response = $this->service->getDetails();
            return $this->success($response);
        }
        catch (\Throwable $e)
        {
            return $this->handleException($e);
        }
    }
	
	public function getCountryName(string $countryId)
    {
        try 
        {
            $response = $this->service->getCountryName($countryId);
            return $this->success($response);
        }
        catch (\Throwable $e)
        {
            return $this->handleException($e);
        }
    }
}