<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('index');

Route::get('/debug-storage', function () {
    $publicStorage = public_path('storage');
    $storageAppPublic = storage_path('app/public');
    
    return [
        'public_path_storage' => [
            'path' => $publicStorage,
            'exists' => file_exists($publicStorage),
            'is_link' => is_link($publicStorage),
            'link_target' => (is_link($publicStorage) && function_exists('readlink')) ? readlink($publicStorage) : 'readlink disabled or not a link',
        ],
        'storage_app_public' => [
            'path' => $storageAppPublic,
            'exists' => file_exists($storageAppPublic),
            'permissions' => substr(sprintf('%o', fileperms($storageAppPublic)), -4),
        ],
        'test_file' => [
            'exists' => \Illuminate\Support\Facades\Storage::disk('public')->exists('profiles/admin/0ABw1Men1jEeF4qU03ngfHbio3J9F2S0WkQdyJyz.png'),
        ]
    ];
});
