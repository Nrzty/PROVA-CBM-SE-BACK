<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthApiKeyMiddleware
{
    private string $apiKeyName = 'X-API-Key';
    private string $apiKeyConfig = 'app.api_key';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $key = $request->header($this->apiKeyName);

        if (! $this->validateApiKey($key))
        {
            return response()->json(
                [
                    'message' => 'Unauthorized'
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
        return $next($request);
    }

    public function validateApiKey(?string $key): bool
    {
       return $key === config($this->apiKeyConfig);
    }
}
