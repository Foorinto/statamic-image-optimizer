<?php

namespace Foorintodev\ImageOptimizer\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;

class DownscaleCommandTest extends TestCase
{
    public function test_la_commande_reduit_les_images_deja_presentes(): void
    {
        Storage::disk('test')->put('existante.jpg', $this->jpeg(5000, 2000));

        $this->artisan('images:downscale', ['container' => 'test'])
            ->expectsOutputToContain('1 image(s) redimensionnée(s).')
            ->assertSuccessful();

        $this->assertSame([2560, 1024], $this->dimensionsOnDisk('existante.jpg'));

        $asset = Asset::find('test::existante.jpg');
        $this->assertSame([2560, 1024], [$asset->width(), $asset->height()]);
        $this->assertSame(Storage::disk('test')->size('existante.jpg'), $asset->size());
    }

    public function test_la_commande_ne_retraite_pas_une_image_deja_reduite(): void
    {
        $this->upload(UploadedFile::fake()->image('grande.jpg', 4000, 3000));
        $contents = Storage::disk('test')->get('grande.jpg');

        $this->artisan('images:downscale', ['container' => 'test'])
            ->expectsOutputToContain('0 image(s) redimensionnée(s).')
            ->assertSuccessful();

        $this->assertSame($contents, Storage::disk('test')->get('grande.jpg'));
    }

    public function test_un_cache_de_metadonnees_perime_ne_provoque_pas_de_reencodage(): void
    {
        // Cas d'un site passé par la v1.0.2 : le cache annonce encore l'ancienne taille.
        $asset = $this->upload(UploadedFile::fake()->image('grande.jpg', 4000, 3000));
        $contents = Storage::disk('test')->get('grande.jpg');

        $stale = array_merge($asset->meta(), ['width' => 4000, 'height' => 3000]);
        $asset->cacheStore()->forever($asset->metaCacheKey(), $stale);
        $this->assertSame(4000, Asset::find('test::grande.jpg')->width(), 'Précondition : cache périmé');

        $this->artisan('images:downscale', ['container' => 'test'])
            ->expectsOutputToContain('0 image(s) redimensionnée(s).')
            ->assertSuccessful();

        $this->assertSame($contents, Storage::disk('test')->get('grande.jpg'), 'Le fichier ne doit pas être ré-encodé');
        $this->assertSame(2560, Asset::find('test::grande.jpg')->width(), 'Le cache doit être réparé');
    }

    public function test_un_container_inconnu_echoue(): void
    {
        $this->artisan('images:downscale', ['container' => 'inconnu'])
            ->expectsOutput('Aucun container trouvé.')
            ->assertFailed();
    }
}
