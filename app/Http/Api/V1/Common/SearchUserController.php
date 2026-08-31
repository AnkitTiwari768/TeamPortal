<?php declare(strict_types=1);

namespace App\Http\Api\V1\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiController;
use DB;

class SearchUserController extends ApiController 
{
    public function __invoke(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'search' => [
                    'bail',
                    'required',
                    'string'
                ]
            ]);
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

            $phrase = $request->has('search') ? $request->query('search') : null;
            
            return $this->getUsers($phrase);
        }
        catch (\Throwable $e)
        {
            return $this->handleException($e);
        }
    }

    public function getUsers(string $phrase)
    {
        $users = DB::table('users')
            ->selectRaw('id, trim(concat_ws(" ", first_name, middle_name, last_name)) as full_name')
            ->whereRaw("trim(concat_ws(' ', first_name, middle_name, last_name)) like ?", ["%".$phrase."%"])
            ->where('status', 1)
            ->get();

        $response['result'] = [];

        if ($users)
        {
            foreach ($users as $user) 
            {
                $response['result'][] = [
                    'id' => $user->id,
                    'text' => $user->full_name
                ];
            }
        }

        return $response;
    }
}