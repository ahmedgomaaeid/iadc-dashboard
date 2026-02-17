<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lesson;

$urls = [
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
    'https://drive.google.com/file/d/1abcDEfgHIjkLMnOpQrStUvWxYz12345/view' => 'drive:1abcDEfgHIjkLMnOpQrStUvWxYz12345',
    'https://drive.google.com/open?id=1abcDEfgHIjkLMnOpQrStUvWxYz12345' => 'drive:1abcDEfgHIjkLMnOpQrStUvWxYz12345',
    'drive.google.com/file/d/1abcDEfgHIjkLMnOpQrStUvWxYz12345' => 'drive:1abcDEfgHIjkLMnOpQrStUvWxYz12345',
    'invalid-url' => null,
];

$results = [];
foreach ($urls as $url => $expected) {
    $result = Lesson::extractVideoId($url);
    $results[] = [
        'url' => $url,
        'expected' => $expected,
        'got' => $result,
        'status' => $result === $expected ? 'PASS' : 'FAIL',
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT);
