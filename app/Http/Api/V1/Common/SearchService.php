<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Common;

use App\Http\Services\ApiService;

use App\Http\Api\V1\Common\Contracts\{
    SearchServiceInterface,
    SearchRepositoryInterface
};

class SearchService extends ApiService implements SearchServiceInterface
{
    public function __construct(protected SearchRepositoryInterface $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    public function getSearchList(string $module, ?string $phrase = null)
    {
        $list = $this->searchRepository->getSearchList($module, $phrase);

        $response['result'] = $list ? 
            array_map(
                fn ($item) => ([
                    'id' => $item->id,
                    'text' => $item->name,
                ]), 
                $list
            ) : 
        []; 

        return $response;
    }
}