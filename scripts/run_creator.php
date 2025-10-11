<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$input = [
    'name'=>'Script User',
    'email'=>'scriptuser@example.com',
    'password'=>'Password123!',
    'password_confirmation'=>'Password123!',
    'role_id'=>1,
    'phone'=>'01700000003',
];
$creator = new App\Actions\Fortify\CreateNewUser();
try{
    $user = $creator->create($input);
    var_export(['ok'=>true,'id'=>$user->id]);
}catch(Exception $e){
    var_export(['ok'=>false,'message'=>$e->getMessage()]);
}
