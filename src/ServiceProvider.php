<?php

namespace Foorintodev\ImageOptimizer;

use Foorintodev\ImageOptimizer\Commands\DownscaleImages;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        DownscaleImages::class,
    ];

    // Le listener src/Listeners/DownscaleLargeImages est enregistré automatiquement
    // par Statamic (mappé sur AssetUploaded via le type de son handle()).

    public function bootAddon()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/image-optimizer.php', 'image-optimizer');

        $this->publishes([
            __DIR__.'/../config/image-optimizer.php' => config_path('image-optimizer.php'),
        ], 'image-optimizer');
    }
}
