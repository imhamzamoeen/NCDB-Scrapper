<?php

namespace App\Http\Middleware;

use Closure;
use DomainException;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class GlobalRequestValidaitonMiddleware
{

    private bool $validationStatus = true;
    /**
     * Handle an incoming request.
     * This is responsible for validating the incoming request if it is coming from the authorized domains
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if( in_array($request->getRequestUri(), config('requestvalidation.excludes'))){
            return $next($request);
        }
        $this->validationStatus = $this->CheckDomain($request);
        throw_unless($this->validationStatus, new DomainException(config("requestvalidation.unregistered_domains_message")));
        if(app()->isProduction() && config('app.token_verification',true)){
         $this->validationStatus = $this->TokenVerification($request);     
         throw_unless($this->validationStatus, new DomainException(config("requestvalidation.unregistered_domains_message")));
        }
        return $next($request);
    }

    private function failed(Request $request): void
    {
        //This method is responsible for logging the failed request to this domains  
        try {
            Log::info(json_encode([
                'inputParams' =>  $request->all(),
                'userIP' => $request->ip(),
                'userAgent' => $request->userAgent(),
                'requestHeaders' => $request->header(),
            ]));
        } catch (Exception $e) {
        }
    }

    public function CheckDomain(Request $request): bool
    {
        //This method is responsible for checking if the request is coming from the authorized domains
        try {
            if (app()->isLocal())
                return true;
            $userDomain = ($request->headers->get('origin') ?? $request->headers->get('referer'));
            $checkResult =   in_array($userDomain, config('requestvalidation.allowed_domains'));
            if (!$checkResult) {
                //Log that failed attempt 
                $this->failed($request);
            }
            return $checkResult;
        } catch (Exception $e) {
            return false;
        }
    }

    private function TokenVerification(Request $request): bool
    {
        try {
            JWT::decode(strval($request->header('authorization_token')), new Key(env('APP_SECRET'), 'HS256'));
            return true;
        } catch (Exception $e) {
            if ($e instanceof \Firebase\JWT\SignatureInvalidException) {
                "Wrong Token Signature";
            } elseif ($e instanceof \Firebase\JWT\ExpiredException) {
                "Expired Time";
            }
            return false;
        }
    }
}
