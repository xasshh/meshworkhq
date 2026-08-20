<?php

/*
 * Where the public disk keeps uploaded avatars and logos.
 *
 * Normally that is storage/app/public, reached from the web through the
 * public/storage symlink. Some shared hosts disable PHP's symlink(), so the
 * link cannot be created at all; there, point this at a real directory inside
 * public/ instead, relative to the project root:
 *
 *     FILESYSTEM_PUBLIC_ROOT=public/storage
 *
 * Leave it unset anywhere the symlink works, and on Fly in particular, where
 * storage/app lives on the /data volume and a directory under public/ would be
 * destroyed by the next deploy.
 */
$publicDiskRoot = env('FILESYSTEM_PUBLIC_ROOT');

$publicDiskRoot = match (true) {
    empty($publicDiskRoot) => storage_path('app/public'),
    str_starts_with($publicDiskRoot, '/') => $publicDiskRoot,
    default => base_path($publicDiskRoot),
};

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => $publicDiskRoot,
            // Root relative on purpose. Building this from APP_URL points every
            // uploaded image at one fixed host, which breaks avatars and logos
            // on localhost, on a tunnel, and anywhere the app is reached by a
            // different domain than APP_URL happens to name.
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
