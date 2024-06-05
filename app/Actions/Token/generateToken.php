<?php

namespace App\Actions\Token;

use App\Http\Middleware\GlobalRequestValidaitonMiddleware;
use App\trait\MessageTrait;
use Exception;
use Illuminate\Http\Request;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class generateToken
{
  use MessageTrait;
  public function generateToken(Request $request)
  {

    try {

      $key = config('app.app_secret');
      if (blank($key) ||  !(app(GlobalRequestValidaitonMiddleware::class)->CheckDomain($request)))
        return $this->errorResponse(message: 'Sorry Not Possible', statusCode: 500);
      $payload = [
        'origin' => ($request->headers->get('origin') ?? $request->headers->get('referer')),
        "iat" => time(),
        "exp" => time() + (60 * 60 * 24 * 30) // Expires in 1 month
      ];

       $jwt = JWT::encode($payload, $key, 'HS256');
        return $this->successResponse(data:['token'=>$jwt]);
    } catch (Exception $e) {
      return false;
    }
  }
}
