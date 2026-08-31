<?php 
declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Traits\HasFileDownload;

abstract class BaseController
{
    use HasFileDownload;
    
    protected function _store(Request $request, string $storeMethod, array $rules, ?string $id = null) 
    {
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }

        $response = $this->queryService->{$storeMethod}($validator->validated(), $id);
       
        return (! $id) ? $this->created($response) : $this->updated($response);
    }
}