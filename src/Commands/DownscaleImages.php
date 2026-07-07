<?php

namespace Foorintodev\ImageOptimizer\Commands;

use Foorintodev\ImageOptimizer\ImageDownscaler;
use Illuminate\Console\Command;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;

class DownscaleImages extends Command
{
    protected $signature = 'images:downscale {container? : Handle du container (tous si omis)}';

    protected $description = 'Redimensionne les images déjà présentes dont un côté dépasse la limite configurée';

    public function handle(): int
    {
        $containers = $this->argument('container')
            ? collect([AssetContainer::find($this->argument('container'))])->filter()
            : AssetContainer::all();

        if ($containers->isEmpty()) {
            $this->error('Aucun container trouvé.');

            return self::FAILURE;
        }

        $count = 0;

        foreach ($containers as $container) {
            foreach (Asset::whereContainer($container->handle()) as $asset) {
                try {
                    if (ImageDownscaler::process($asset)) {
                        $this->line("  ↓ {$asset->path()}");
                        $count++;
                    }
                } catch (\Throwable $e) {
                    $this->warn("  ! {$asset->path()} : {$e->getMessage()}");
                }
            }
        }

        $this->info("{$count} image(s) redimensionnée(s).");

        return self::SUCCESS;
    }
}
