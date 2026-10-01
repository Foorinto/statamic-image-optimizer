<?php

namespace Foorintodev\ImageOptimizer\Tests;

use Foorintodev\ImageOptimizer\ServiceProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');
        AssetContainer::make('test')->disk('test')->save();
    }

    /** Envoie un fichier dans le container « test », comme le fait le CP. */
    protected function upload(UploadedFile $file): AssetContract
    {
        return Asset::make()->container('test')->path($file->getClientOriginalName())->upload($file);
    }

    /** Contenu binaire d'un JPEG de la taille demandée. */
    protected function jpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($image, null, 90);

        return ob_get_clean();
    }

    /** Dimensions réelles du fichier sur le disque : [largeur, hauteur]. */
    protected function dimensionsOnDisk(string $path): array
    {
        return array_slice(getimagesizefromstring(Storage::disk('test')->get($path)), 0, 2);
    }
}
