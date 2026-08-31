<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Common;

use App\Http\Controllers\ApiController;

use App\Http\Api\V1\Common\Contracts\SearchServiceInterface;

use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class SearchController extends ApiController 
{
    public function __construct(protected SearchServiceInterface $searchService) 
    {
        $this->searchService = $searchService;
    }

    public function __invoke(Request $request, string $module) 
    {
        try 
        {
            $validator = $this->validator($request->all());

            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

            $phrase = $request->has('search') ? $request->query('search') : null;
            $result = $this->searchService->getSearchList($module, $phrase);

            return $this->success($result);
        }
        catch (\Throwable $e)
        {
            return $this->handleException($e);
        }
    }

    private function validator(array $payload) 
    {
        return Validator::make($payload, [
            'search' => [
                'bail',
                'required',
                'string'
            ]
        ]);
    }
}