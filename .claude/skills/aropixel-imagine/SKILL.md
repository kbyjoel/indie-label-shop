---
name: aropixel-imagine
description: >
  Using the aropixel_imagine_filter Twig filter for image display with automatic placeholder handling.
  Use this skill whenever displaying images from Band, Album, Release, or any entity that uses
  AttachedImage / ImageInterface. The filter handles null/missing images automatically — never
  write manual null checks or placeholder <img> fallbacks.
---

# Skill: aropixel_imagine_filter

## Origin

The filter is provided by **`aropixel/admin-bundle`** (vendor). No installation or registration needed — it is always available in Twig.

---

## Twig syntax

```twig
{{ image | aropixel_imagine_filter('filter_name') }}
```

- `image` — accepts `ImageInterface`, `AttachedImage`, or `null`
- `'filter_name'` — a LiipImagine filter set name defined in `liip_imagine.yaml`

The filter **always returns a valid URL**. When `image` is `null` or the file is missing, it generates a gray placeholder sized to match the requested filter's dimensions.

---

## Entity image access

### Band → BandImage (AttachedImage)

```twig
{{ band.image | aropixel_imagine_filter('band_card') }}
```

`band.image` is `null` when no image is uploaded → placeholder is returned automatically.

### Album → AlbumImage (AttachedImage), accessed via `artwork`

```twig
{{ album.artwork | aropixel_imagine_filter('album_card') }}
```

`album.artwork` is `null` when no artwork is uploaded → placeholder returned automatically.

### Generic pattern

```twig
<img src="{{ entity.image | aropixel_imagine_filter('my_filter') }}"
     alt="{{ entity.image ? entity.image.attrAlt : '' }}">
```

Never add `{% if entity.image %}` guards just for the `src` attribute — the filter handles it.

---

## Defining filter sets

Add custom filter sets in `application/config/packages/liip_imagine.yaml` under the root `liip_imagine.filter_sets` key (not under `when@prod` or `when@dev`):

```yaml
liip_imagine:
    driver: "imagick"
    filter_sets:
        album_card:
            quality: 80
            filters:
                thumbnail: { size: [400, 400], mode: outbound }

        album_artwork:
            quality: 85
            filters:
                thumbnail: { size: [800, 800], mode: inset }

        band_card:
            quality: 80
            filters:
                thumbnail: { size: [400, 400], mode: outbound }
```

**Modes:**
- `outbound` — crops to exact dimensions (fills the box, may crop edges)
- `inset` — fits inside the box without cropping (may leave transparent/white edges)

After adding a new filter, **clear the cache**:
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

Do **not** use admin filter names in front-end templates — define dedicated front filter sets for correct sizing and SEO.

---

## Placeholder behavior

When image is `null` or the file is absent:
- Dimensions are read from the filter set config (`thumbnail.size` or `scale.dim`)
- A **gray (#d5d5d5) background** placeholder is returned
- Fallback default: 200×200 if no size found in config

No additional code needed — the placeholder is transparent to the template.

---

## Complete example — album card partial

```twig
{# templates/front/partials/_album_card.html.twig #}
<article class="group">
    <a href="{{ path('front_album_show', {slug: album.slug}) }}">
        <img src="{{ album.artwork | aropixel_imagine_filter('album_card') }}"
             alt="{{ album.artwork ? album.artwork.attrAlt : album.title }}"
             class="w-full aspect-square object-cover"
             loading="lazy">
        <div class="mt-2">
            <h3 class="font-display font-semibold">{{ album.title }}</h3>
            <p class="text-muted text-sm">{{ album.releaseDate|date('Y') }}</p>
        </div>
    </a>
</article>
```
