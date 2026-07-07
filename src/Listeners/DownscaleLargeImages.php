<?php

namespace Foorintodev\ImageOptimizer\Listeners;

use Foorintodev\ImageOptimizer\ImageDownscaler;
use Illuminate\Support\Facades\Log;
use Statamic\Events\AssetUploaded;

/**
 * Réduit les images trop grandes dès l'upload (ex. 8256×5504, 23 Mo). Les déclinaisons
 * servies au visiteur sont générées par Glide dans les templates du projet.
 */
class DownscaleLargeImages
{
    public function handle(AssetUploaded $event): void
    {
        if (! config('image-optimizer.auto_downscale_on_upload', true)) {
            return;
        }

        try {
            ImageDownscaler::process($event->asset);
        } catch (\Throwable $e) {
            Log::warning("Redimensionnement à l'upload échoué pour {$event->asset->path()} : {$e->getMessage()}");
        }
    }
}
