<?php

/*
| Raíz del volumen de datos, resuelta aquí y no en un helper.
|
| Los archivos de config se cargan en orden alfabético, así que este es el
| primer sitio donde la ruta se necesita y no puede depender de config() de
| otro archivo. Y tiene que salir de env() aquí dentro: en produccción la
| configuración se cachea y env() devuelve null fuera de los config, con lo que
| los archivos acabarían dentro del proyecto sin previo aviso.
*/
$media_environment = env('APP_ENV', 'production') === 'production' ? 'production' : 'test';
$media_root = rtrim((string) env('MEDIA_ROOT', storage_path('app/media')), '/\\')
    .DIRECTORY_SEPARATOR.$media_environment;

return [

    /*
    |--------------------------------------------------------------------------
    | Volumen de datos
    |--------------------------------------------------------------------------
    |
    | Se exponen para que media_root() y media_environment() los lean sin
    | volver a tocar env(). Ambos incluyen ya la carpeta del entorno.
    |
    */

    'media_root' => $media_root,

    'media_environment' => $media_environment,

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
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
        | Volumen de datos montado fuera del proyecto (MEDIA_ROOT). Se divide en dos
        | discos para que los archivos servidos por la web y los de acceso restringido
        | nunca compartan carpeta:
        |
        |   {MEDIA_ROOT}/public   -> imágenes y logos, accesibles vía /media
        |   {MEDIA_ROOT}/private  -> facturación electrónica y demás documentos
        |
        | En local, si no se define MEDIA_ROOT, se usa storage/app/media.
        */

        'media' => [
            'driver' => 'local',
            'root' => $media_root.DIRECTORY_SEPARATOR.'public',
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/media',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'documents' => [
            'driver' => 'local',
            'root' => $media_root.DIRECTORY_SEPARATOR.'private',
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
        public_path('media') => $media_root.DIRECTORY_SEPARATOR.'public',
    ],

];
