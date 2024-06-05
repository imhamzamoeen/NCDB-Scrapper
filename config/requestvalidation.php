<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Allowed Domains 
    |--------------------------------------------------------------------------
    |
    | This would be the list of allowed domains for this micorservices 
    |
    */

  'allowed_domains' => json_decode(env('allowed_domains', '["localhost"]')),
  'unregistered_domains_message' => "Sorry, Your Domain is not Registered",
  'token_error' => "Sorry,Token could not be auhtenticated ",
  // this would be a route to get a new token 
  'excludes' => [
    '/api/GetToken',
    '/test'
  ],

];
