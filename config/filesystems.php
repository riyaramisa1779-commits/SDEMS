<?php

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

        /*
         * Evidence disk — PRIVATE storage for all uploaded evidence files.
         *
         * Security decisions:
         * - Root is storage/app/evidence — outside the public web root.
         * - 'serve' is intentionally FALSE: files must never be served directly.
         *   All downloads must go through a controller that verifies permissions,
         *   logs the access, and streams the file with appropriate headers.
         * - 'visibility' is not set (defaults to private).
         * - For production: swap driver to 's3' and set EVIDENCE_DISK=s3 in .env.
         *   The S3 bucket must have Block Public Access enabled and no bucket policy
         *   granting public read. Use pre-signed URLs with short TTLs for downloads.
         */
        'evidence' => [
            'driver' => env('EVIDENCE_DISK_DRIVER', 'local'),
            'root'   => storage_path('app/evidence'),
            'serve'  => false,  // NEVER serve evidence files directly
            'throw'  => true,   // Throw exceptions on storage errors (fail loudly)
            'report' => true,

            // S3 settings (active when EVIDENCE_DISK_DRIVER=s3)
            'key'                  => env('AWS_ACCESS_KEY_ID'),
            'secret'               => env('AWS_SECRET_ACCESS_KEY'),
            'region'               => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket'               => env('EVIDENCE_S3_BUCKET', env('AWS_BUCKET')),
            'url'                  => env('AWS_URL'),
            'endpoint'             => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
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
