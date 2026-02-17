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

foreach ($urls as $url => $expected) {
    $result = Lesson::extractVideoId($url);
    echo "URL: $url\n";
    echo "Expected: " . ($expected ?? 'NULL') . "\n";
    echo "Got:      " . ($result ?? 'NULL') . "\n";
    echo ($result === $expected ? 'PASS' : 'FAIL') . "\n";
    echo str_repeat('-', 40) . "\n";
}
