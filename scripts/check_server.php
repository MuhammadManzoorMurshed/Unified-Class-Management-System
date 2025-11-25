<?php
$ch = curl_init('http://127.0.0.1:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$res = curl_exec($ch);
$info = curl_getinfo($ch);
var_export([$info['http_code'], strlen((string)$res)]);
