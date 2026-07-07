# Foorintodev Image Optimizer (addon Statamic)

Réduit automatiquement les images trop grandes **à l'upload** dans Statamic 6, et
fournit une commande pour traiter les images **déjà présentes**. Léger, sans
dépendance externe (Intervention Image est déjà livré avec Statamic).

- À l'upload : le plus grand côté de l'image est ramené à `max_dimension` (défaut
  **2560 px**), **sans jamais agrandir**, format d'origine conservé, métadonnées
  (dimensions/poids) rafraîchies.
- Évite de stocker des originaux démesurés (ex. 8256×5504, 23 Mo) et les plantages
  mémoire lors des manipulations d'images.

> **Complément côté front** : cet addon gère l'**original**. Pour servir des
> déclinaisons légères au visiteur (WebP, tailles adaptées, `srcset`), utilisez
> **Glide** dans vos templates — c'est natif à Statamic :
> ```antlers
> {{ images }}<img src="{{ glide :src="id" width="800" format="webp" quality="80" }}">{{ /images }}
> ```

## Installation

```bash
composer config repositories.foorintodev-image-optimizer vcs https://github.com/Foorinto/statamic-image-optimizer.git
composer config github-oauth.github.com <PAT>          # repo privé
composer require foorintodev/statamic-image-optimizer:^1.0
php artisan vendor:publish --tag=image-optimizer       # (optionnel) publie la config
```

Rien d'autre à faire : la réduction à l'upload s'active toute seule.

## Configuration (`config/image-optimizer.php`)

- `max_dimension` — plus grand côté autorisé (défaut 2560)
- `quality` — qualité de ré-encodage (défaut 85)
- `formats` — formats traités (`jpg, jpeg, png, webp`)
- `auto_downscale_on_upload` — activer/désactiver l'action à l'upload
- `excluded_containers` — handles de containers à ne jamais toucher

Tout est surchargeable par `.env` : `IMAGE_MAX_DIMENSION`, `IMAGE_QUALITY`,
`IMAGE_AUTO_DOWNSCALE`.

## Images déjà présentes

```bash
php artisan images:downscale            # tous les containers
php artisan images:downscale zeutzius   # un container précis
```
Parcourt les assets et réduit ceux dont un côté dépasse `max_dimension`.
