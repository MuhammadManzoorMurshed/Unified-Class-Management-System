<?php
$url = 'http://127.0.0.1:8000/register';
$data = [
    'name' => 'Auto Test',
    'email' => 'autotest@example.com',
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
    'role_id' => 1,
    'phone' => '01700000001',
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$res = curl_exec($ch);
$errno = curl_errno($ch);
$err = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);
var_export(['errno'=>$errno, 'error'=>$err, 'http_code'=>$info['http_code'], 'body'=>substr($res,0,1000)]);
