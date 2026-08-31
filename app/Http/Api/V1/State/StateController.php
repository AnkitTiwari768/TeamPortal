<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\State;
use App\Http\Controllers\ApiController;
use App\Http\Api\V1\State\StateValidation as Validation;
use Illuminate\Http\Request;

class StateController extends ApiController 
{
    public function __construct(private StateService $service) 
    {
        $this->service = $service;
    }

    public function index()
    {
        try 
        {
            $result = $this->service->getStates();
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
            $validator = Validation::getRules($request);
            
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
	
	public function getByCountry(string $countryId)
    {
        try 
        {
            $response = $this->service->getByCountry($countryId);
            return $this->success($response);
        }
        catch (\Throwable $e)
        {
            return $this->handleException($e);
        }
    }
}