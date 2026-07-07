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

## Configuration

`max_dimension` est **à la fois le seuil et la cible** : une image dont un côté dépasse
cette valeur est réduite jusqu'à cette valeur (jamais agrandie).

### Le plus simple — dans le `.env`
```bash
IMAGE_MAX_DIMENSION=2000     # réduit tout ce qui dépasse 2000 px (défaut 2560)
IMAGE_QUALITY=85             # qualité de ré-encodage 0-100 (défaut 85)
IMAGE_AUTO_DOWNSCALE=true    # false = désactive la réduction à l'upload
```
Puis `php artisan config:clear`. Prise en compte **immédiate**, aucun déploiement requis.

### Ou dans le fichier de config
```bash
php artisan vendor:publish --tag=image-optimizer   # crée config/image-optimizer.php
```

| Clé | Défaut | Rôle |
|---|---|---|
| `max_dimension` | 2560 | Plus grand côté autorisé (seuil de déclenchement **et** taille cible) |
| `quality` | 85 | Qualité de ré-encodage (0-100) |
| `formats` | jpg, jpeg, png, webp | Formats traités (le reste est ignoré) |
| `auto_downscale_on_upload` | true | Activer / désactiver la réduction à l'upload |
| `excluded_containers` | (vide) | Handles de containers à ne jamais toucher |

> Après avoir baissé `max_dimension`, tu peux réappliquer la limite aux images déjà
> présentes avec `php artisan images:downscale` (voir ci-dessous).

## Images déjà présentes

```bash
php artisan images:downscale            # tous les containers
php artisan images:downscale zeutzius   # un container précis
```
Parcourt les assets et réduit ceux dont un côté dépasse `max_dimension`.
