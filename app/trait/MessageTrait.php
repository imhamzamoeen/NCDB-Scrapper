<?php

namespace App\trait;

use Illuminate\Http\JsonResponse;

trait MessageTrait
{
  protected function successResponse( $data = null,int $statusCode = 200): JsonResponse
  {
    return response()->json([
      'success' => true,
      'data' => $data,
    ], $statusCode);
  }

  /**
   * Send an error response.
   *
   * @param  string|array|null  $message
   * @param  int  $statusCode
   * @return \Illuminate\Http\JsonResponse
   */
  protected function errorResponse(string|array|null $message, int $statusCode=403): JsonResponse
  {
    return response()->json([
      'success' => false,
      'error' => $message,
    ], $statusCode);
  }

  /**
   * Send an exception response.
   *
   * @param  \Throwable  $exception
   * @param  int  $statusCode
   * @return \Illuminate\Http\JsonResponse
   */
  protected function exceptionResponse(\Throwable $exception,int $statusCode=500): JsonResponse
  {
    return response()->json([
      'success' => false,
      'error' => $exception->getMessage(),
    ], $statusCode);
  }
}
