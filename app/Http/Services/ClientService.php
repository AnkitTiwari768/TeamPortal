<?php 

declare(strict_types=1);

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Request as ClientRequest; 

class ClientService 
{
    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer '. $this->getAccessToken(),
            'Content-Type' => 'application/json',
        ];
    }

    protected function get(string $uri, ?array $query = [], ?int $isAuth = 1): mixed
    {
        $headers = $this->getHeaders();

        $url = url('api/'. $uri);

        if ($query) 
        {
            $url .= '?'.  http_build_query($query); 
        }
       

        $response = ($isAuth === 1) 
            ? Http::withToken($this->getAccessToken())->get($url) 
            : Http::get($url);
        
        return $response->object();
    }

    protected function post(string $uri, ?array $payload = [], ?int $isAuth = 1): mixed 
    {
        $headers = $this->getHeaders();
        $url = url('api/'. $uri);
        
        $response = ($isAuth) 
            ? Http::withHeaders($headers)->post($url, $payload) 
            : Http::post($url, $payload);
        
        return $response->object();
    }
    
    public function getAccessToken()
    {
        $auth = session('auth');
        return $auth ? $auth->token : null;
    }
}