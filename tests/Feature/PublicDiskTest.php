<?php

/**
 * Uploaded avatars and logos are only reachable if the public disk writes
 * somewhere the web server can actually serve, and the URL it generates is
 * host independent. Both have broken in deployment before: once through an
 * absolute URL built from APP_URL, once on a host where PHP's symlink() is
 * disabled so public/storage could never be created.
 */
it('writes to storage/app/public by default', function () {
    expect(config('filesystems.disks.public.root'))->toBe(storage_path('app/public'));
});

it('serves uploads from a root relative url, never one built from APP_URL', function () {
    config(['app.url' => 'https://some-other-host.test']);

    expect(Storage::disk('public')->url('avatars/example.jpg'))
        ->toBe('/storage/avatars/example.jpg');
});

it('can point the public disk at a real directory when the host cannot symlink', function () {
    putenv('FILESYSTEM_PUBLIC_ROOT=public/storage');

    try {
        $config = require config_path('filesystems.php');
    } finally {
        putenv('FILESYSTEM_PUBLIC_ROOT');
    }

    expect($config['disks']['public']['root'])->toBe(base_path('public/storage'))
        // The URL must not change with the root, or every existing image breaks.
        ->and($config['disks']['public']['url'])->toBe('/storage');
});

it('accepts an absolute path for the public disk root', function () {
    putenv('FILESYSTEM_PUBLIC_ROOT=/srv/uploads');

    try {
        $config = require config_path('filesystems.php');
    } finally {
        putenv('FILESYSTEM_PUBLIC_ROOT');
    }

    expect($config['disks']['public']['root'])->toBe('/srv/uploads');
});

it('keeps verification documents off the public disk', function () {
    // A CAC certificate must never be web reachable, whatever the public disk
    // is pointed at.
    expect(config('filesystems.disks.local.root'))->toBe(storage_path('app/private'))
        ->and(config('filesystems.disks.local'))->not->toHaveKey('url');
});
