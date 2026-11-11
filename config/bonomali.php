<?php

return [

    /*
    | Owner admin login created by `php artisan db:seed`.
    | Leave empty to skip; never commit real values.
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Owner'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
