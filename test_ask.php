<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create(
    '/coach/ask',
    'POST',
    ['question' => 'how do i servive this 10 days now', 'personality' => 'encouraging']
);
$response = $kernel->handle($request);
echo $response->getStatusCode() . "\n";
echo $response->getContent();
