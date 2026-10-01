<?php

namespace Foorintodev\ImageOptimizer\Tests;

use Foorintodev\ImageOptimizer\Listeners\DownscaleLargeImages;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Events\AssetUploaded;
use Statamic\Facades\Asset;

class DownscaleOnUploadTest extends TestCase
{
    public function test_le_listener_est_enregistre_une_seule_fois(): void
    {
        $listeners = collect(app('events')->getRawListeners()[AssetUploaded::class] ?? [])
            ->filter(fn ($l) => (is_array($l) ? $l[0] : $l) === DownscaleLargeImages::class);

        $this->assertCount(1, $listeners);
    }

    public function test_une_image_trop_grande_est_reduite_a_l_upload(): void
    {
        $this->upload(UploadedFile::fake()->image('grande.jpg', 4000, 3000));

        $this->assertSame([2560, 1920], $this->dimensionsOnDisk('grande.jpg'));
    }

    public function test_les_metadonnees_sont_a_jour_sur_l_objet_et_dans_le_cache(): void
    {
        // Régression v1.0.2 : writeMeta() laissait les anciennes valeurs en cache.
        $asset = $this->upload(UploadedFile::fake()->image('grande.jpg', 4000, 3000));

        // Même objet : c'est lui que le CP renvoie juste après l'upload.
        $this->assertSame([2560, 1920], [$asset->width(), $asset->height()]);

        // Nouvel objet : relit le cache de métadonnées.
        $fresh = Asset::find('test::grande.jpg');
        $this->assertSame([2560, 1920], [$fresh->width(), $fresh->height()]);
        $this->assertSame(Storage::disk('test')->size('grande.jpg'), $fresh->size());
    }

    public function test_une_image_assez_petite_n_est_pas_touchee(): void
    {
        $file = UploadedFile::fake()->image('petite.jpg', 800, 600);
        $original = file_get_contents($file->getPathname());

        $this->upload($file);

        $this->assertSame($original, Storage::disk('test')->get('petite.jpg'));
    }

    public function test_la_dimension_maximale_est_configurable(): void
    {
        config(['image-optimizer.max_dimension' => 1000]);

        $this->upload(UploadedFile::fake()->image('grande.jpg', 4000, 3000));

        $this->assertSame([1000, 750], $this->dimensionsOnDisk('grande.jpg'));
    }

    public function test_le_format_d_origine_est_conserve(): void
    {
        $this->upload(UploadedFile::fake()->image('grande.png', 3000, 3000));

        $info = getimagesizefromstring(Storage::disk('test')->get('grande.png'));
        $this->assertSame([2560, 2560, 'image/png'], [$info[0], $info[1], $info['mime']]);
    }

    public function test_un_format_non_liste_n_est_pas_touche(): void
    {
        config(['image-optimizer.formats' => ['png']]);
        $file = UploadedFile::fake()->image('grande.jpg', 4000, 3000);
        $original = file_get_contents($file->getPathname());

        $this->upload($file);

        $this->assertSame($original, Storage::disk('test')->get('grande.jpg'));
    }

    public function test_un_container_exclu_n_est_pas_touche(): void
    {
        config(['image-optimizer.excluded_containers' => ['test']]);
        $file = UploadedFile::fake()->image('grande.jpg', 4000, 3000);
        $original = file_get_contents($file->getPathname());

        $this->upload($file);

        $this->assertSame($original, Storage::disk('test')->get('grande.jpg'));
    }

    public function test_la_reduction_a_l_upload_peut_etre_desactivee(): void
    {
        config(['image-optimizer.auto_downscale_on_upload' => false]);

        $this->upload(UploadedFile::fake()->image('grande.jpg', 4000, 3000));

        $this->assertSame([4000, 3000], $this->dimensionsOnDisk('grande.jpg'));
    }

    public function test_un_fichier_qui_n_est_pas_une_image_est_ignore(): void
    {
        $this->upload(UploadedFile::fake()->create('document.pdf', 20, 'application/pdf'));

        $this->assertTrue(Storage::disk('test')->exists('document.pdf'));
    }
}
