<?php

namespace App\Helpers;

if (!function_exists('toSnakeCase')) {
  function toSnakeCase(string $inputString) : string 
  {
    // Replace spaces and special characters with underscores
    $snakeCaseString = preg_replace('/[^A-Za-z0-9]+/', '_', $inputString);
    // Convert to lowercase
    $snakeCaseString = strtolower($snakeCaseString);
    // Remove leading and trailing underscores
    $snakeCaseString = trim($snakeCaseString, '_');
    return $snakeCaseString;
  }
}
