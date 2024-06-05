<?php

namespace App\Http\Controllers;

use App\Actions\Token\generateToken;
use App\trait\MessageTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class JWTTokenController extends Controller
{
    //

    use MessageTrait;

    public function generateToken(Request  $request, generateToken $action)
    {

        try {
            $executed = RateLimiter::attempt(
                'user-generate-token-api' . $request->ip(),
                $perMinute = 5,
                function () use ($request, $action) {
                    return $action->generateToken($request);
                },
                60
            );
            return $executed ?: $this->errorResponse(statusCode: 429, message: 'Too Many Attempts please try again in ' . RateLimiter::availableIn('user-generate-token-api' . $request->ip()) . 'minutes');
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }
}
