<?php

namespace Foorintodev\ImageOptimizer;

use Intervention\Image\ImageManager;
use Statamic\Contracts\Assets\Asset;

/**
 * Réduit un original d'image trop grand (le plus grand côté est ramené à la limite
 * configurée), sans jamais agrandir, en conservant le format. Utilisé à l'upload
 * (listener) et par la commande `images:downscale`.
 */
class ImageDownscaler
{
    /** Redimensionne l'original si nécessaire. Retourne true si le fichier a été modifié. */
    public static function process(Asset $asset, ?int $max = null, ?int $quality = null): bool
    {
        $max ??= (int) config('image-optimizer.max_dimension', 2560);
        $quality ??= (int) config('image-optimizer.quality', 85);
        $formats = (array) config('image-optimizer.formats', ['jpg', 'jpeg', 'png', 'webp']);
        $excluded = (array) config('image-optimizer.excluded_containers', []);

        if (! $asset->isImage()) {
            return false;
        }
        if (in_array($asset->container()->handle(), $excluded, true)) {
            return false;
        }

        $ext = strtolower((string) $asset->extension());
        if (! in_array($ext, $formats, true)) {
            return false;
        }

        $width = (int) $asset->width();
        $height = (int) $asset->height();

        if ($width === 0 || $height === 0) {
            return false;
        }
        if ($width <= $max && $height <= $max) {
            return false;
        }

        $disk = $asset->disk();
        $path = $asset->path();

        $manager = method_exists(ImageManager::class, 'gd')
            ? ImageManager::gd()
            : new ImageManager(new \Intervention\Image\Drivers\Gd\Driver);

        $image = $manager->read($disk->get($path));
        $image->scaleDown($max, $max);
        $disk->put($path, (string) $image->encodeByExtension($ext, quality: $quality));

        // Rafraîchir les métadonnées en cache (dimensions, poids).
        $asset->writeMeta($asset->generateMeta());

        return true;
    }
}
