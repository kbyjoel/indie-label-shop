---
name: aropixel-imagine
description: >
  Using the aropixel_imagine_filter Twig filter for image display with automatic placeholder handling,
  and reading/writing uploaded files via Flysystem with aropixel/admin-bundle.
  Use this skill whenever displaying images from any entity that uses AttachedImage / ImageInterface.
  The filter handles null/missing images automatically — never write manual null checks or placeholder
  <img> fallbacks.
  Also use this skill when a custom service or handler needs to read or write a file that was
  uploaded via the admin — to know which Flysystem disk to inject.
---

# Skill: aropixel/admin-bundle — images & Flysystem storage

## Twig filter: aropixel_imagine_filter

### Origin

The filter is provided by **`aropixel/admin-bundle`** (vendor). No installation or registration needed — it is always available in Twig.

### Syntax

```twig
{{ image | aropixel_imagine_filter('filter_name') }}
```

- `image` — accepts `ImageInterface`, `AttachedImage`, or `null`
- `'filter_name'` — a LiipImagine filter set name defined in `liip_imagine.yaml`

The filter **always returns a valid URL**. When `image` is `null` or the file is missing, it generates a gray placeholder sized to match the requested filter's dimensions.

### Entity image access

```twig
{{ entity.image | aropixel_imagine_filter('my_filter') }}
```

`entity.image` returns `null` when no image is uploaded → placeholder is returned automatically.

```twig
<img src="{{ entity.image | aropixel_imagine_filter('my_filter') }}"
     alt="{{ entity.image ? entity.image.attrAlt : '' }}">
```

Never add `{% if entity.image %}` guards just for the `src` attribute — the filter handles it.

---

## Defining filter sets

Add custom filter sets in `config/packages/liip_imagine.yaml` under the root `liip_imagine.filter_sets` key (not under `when@prod` or `when@dev`):

```yaml
liip_imagine:
    driver: "imagick"
    filter_sets:
        my_card:
            quality: 80
            filters:
                thumbnail: { size: [400, 400], mode: outbound }

        my_detail:
            quality: 85
            filters:
                thumbnail: { size: [800, 800], mode: inset }
```

**Modes:**
- `outbound` — crops to exact dimensions (fills the box, may crop edges)
- `inset` — fits inside the box without cropping (may leave transparent/white edges)

After adding a new filter, clear the cache:
```bash
castor docker:builder -- php bin/console cache:clear
```

---

## Pre-existing filter sets (from aropixel/admin-bundle)

These are available without configuration — do not redefine them:

| Filter name | Dimensions | Use |
|---|---|---|
| `admin_thumbnail` | 400×400 | Admin list previews |
| `admin_preview` | widen to 800px | Admin lightbox |
| `admin_crop` | widen to 600px | Admin crop tool |

Do **not** use admin filter names in front-end templates — define dedicated front-end filter sets for correct sizing and SEO.

---

## Placeholder behavior

When image is `null` or the file is absent:
- Dimensions are read from the filter set config (`thumbnail.size` or `scale.dim`)
- A **gray (#d5d5d5) background** placeholder is returned
- Fallback default: 200×200 if no size found in config

---

## Flysystem storage — disks defined by the bundle

### How disks are registered

`private.storage` and `public.storage` are registered by **`AropixelAdminExtension::prepend()`** — they must **not** be declared in the project's `config/packages/flysystem.yaml`. The project can override them under `when@prod` if needed (e.g. to switch to S3).

| Disk | Dev (adapter: `local`) | Prod (adapter: `asyncaws`, typical) |
|---|---|---|
| `private.storage` | `%kernel.project_dir%/private/` | S3-compatible bucket, prefix `private` |
| `public.storage` | `%kernel.project_dir%/public/` | S3-compatible bucket, prefix `public` |

### Which disk for which file

- **`private.storage`** — any uploaded file that must not be publicly accessible (audio masters, admin attachments). `UploadFileListener` writes there automatically on every `postPersist`.
- **`public.storage`** — images whose URLs are exposed directly; LiipImagine reads from this disk via the `flysystem` loader.
- Any additional disk (e.g. `previews.storage`) is project-specific and must be declared in `flysystem.yaml`.

### Reading an uploaded file in a custom service

Inject `FilesystemOperator $privateStorage` (parameter name must match the disk name in camelCase):

```php
use League\Flysystem\FilesystemOperator;

class MyHandler
{
    public function __construct(
        private FilesystemOperator $privateStorage,
    ) {}

    public function handle(MyEntity $entity): void
    {
        $filename = $entity->getFile()->getFilename();

        // Copy to a local temp file (required for tools like FFmpeg that need a real path)
        $stream = $this->privateStorage->readStream($filename);
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('file_', true);
        file_put_contents($tmpPath, $stream);

        // ... process $tmpPath ...

        unlink($tmpPath);
    }
}
```

**Never** reconstruct the path via `$projectDir . '/private/' . $filename` — this breaks in production (S3).

### Writing a generated non-public file

Use `private.storage` with a dedicated sub-path:

```php
$stream = fopen($tmpOutputPath, 'r');
$this->privateStorage->writeStream('generated/' . $subPath . '/' . $filename, $stream);
fclose($stream);
```
