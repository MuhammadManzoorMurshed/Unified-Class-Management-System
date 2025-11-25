<?php
$ch = curl_init('http://127.0.0.1:8000/api/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
$post = [
    'name' => 'Api User',
    'email' => 'apiuser@example.com',
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
    'role_id' => 1,
    'phone' => '01700000002',
];
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$res = curl_exec($ch);
$info = curl_getinfo($ch);
var_export(['http_code' => $info['http_code'], 'body' => substr((string)$res,0,1200)]);
