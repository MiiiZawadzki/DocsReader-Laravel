<?php

return [

    /*
    | Name of the HttpOnly cookie that carries the JWT
    */
    'name' => env('AUTH_COOKIE_NAME', 'jwt_token'),

    /*
    | Cookie scope. For cross-subdomain SPAs (app.* + api.*) set a leading-dot
    | parent domain (e.g. ".example.com"). Leave null for single-host setups.
    */
    'domain' => env('AUTH_COOKIE_DOMAIN'),

    'path' => env('AUTH_COOKIE_PATH', '/'),

    'secure' => filter_var(env('AUTH_COOKIE_SECURE', true), FILTER_VALIDATE_BOOLEAN),

    /*
    | Lax is sent on top-level navigation but blocks cross-site POST/PUT/etc,
    | which gives CSRF protection without an explicit token when paired with
    | strict CORS allowlisting on the API.
    */
    'same_site' => env('AUTH_COOKIE_SAME_SITE', 'lax'),
];
