<?php

use Laravel\Fortify\Features;

return [
    // ❶ Fortify views বন্ধ রাখছি কারণ আমরা API-first + নিজের ফ্রন্টেন্ড/SPA ব্যবহার করব
    'views' => false,
    'middleware' => ['api'],

    // ❷ ইউজারনেম ফিল্ড
    'username' => 'email',

    // ❸ ফিচারস – UCMS Sprint-1 অনুযায়ী
    'features' => [
        Laravel\Fortify\Features::registration(),
        Laravel\Fortify\Features::resetPasswords(),
        Laravel\Fortify\Features::updateProfileInformation(),
        Laravel\Fortify\Features::updatePasswords(),
        Laravel\Fortify\Features::resetPasswords(),
    ],

    // ❹ লিমিটারস (ঐচ্ছিক কিন্তু ভালো প্র্যাকটিস)
    'limiters' => [
        'login' => 'login',
        'two-factor' => null,
    ],
];