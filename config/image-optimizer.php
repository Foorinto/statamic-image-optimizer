<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dimension maximale
    |--------------------------------------------------------------------------
    | Le plus grand côté de l'image originale est ramené à cette valeur (px).
    | Les images plus petites ne sont jamais agrandies.
    */
    'max_dimension' => env('IMAGE_MAX_DIMENSION', 2560),

    /*
    |--------------------------------------------------------------------------
    | Qualité de ré-encodage (0-100)
    |--------------------------------------------------------------------------
    */
    'quality' => env('IMAGE_QUALITY', 85),

    /*
    |--------------------------------------------------------------------------
    | Formats traités
    |--------------------------------------------------------------------------
    | Formats matriciels redimensionnés. Le format d'origine est conservé.
    | (Les SVG et autres ne sont jamais touchés.)
    */
    'formats' => ['jpg', 'jpeg', 'png', 'webp'],

    /*
    |--------------------------------------------------------------------------
    | Réduction automatique à l'upload
    |--------------------------------------------------------------------------
    | true = redimensionne dès qu'une image est envoyée dans le CP.
    */
    'auto_downscale_on_upload' => env('IMAGE_AUTO_DOWNSCALE', true),

    /*
    |--------------------------------------------------------------------------
    | Containers exclus
    |--------------------------------------------------------------------------
    | Handles de containers à ne jamais redimensionner (ex. logos, PDF…).
    */
    'excluded_containers' => [],

];
