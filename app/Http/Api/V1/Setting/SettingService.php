<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Setting;

use App\Http\Services\ApiService;
use App\Http\Api\V1\Setting\Setting as Model;
use DB;

class SettingService extends ApiService 
{
    public function getSettings()
    {
       return Model::all()->toArray();
    }

    public function create(array $payload)
    {
        return Model::create($payload);
    }

    public function update(array $payload, string $id)
    {
        $model = Model::findOrFail($id);
        return $model->fill($payload)->save();
    }
}