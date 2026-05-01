# Music Catalogue — Albums & Artists

Documentation for Step 2: album listing, artist pages, and homepage data.

## Routes

| Route name | URL (multi-locale) | Controller |
|---|---|---|
| `front_album_index` | `/{_locale}/albums` | `AlbumController::index()` |
| `front_album_show` | `/{_locale}/album/{slug}` | `AlbumController::show()` |
| `front_band_index` | `/{_locale}/artistes` | `BandController::index()` |
| `front_band_show` | `/{_locale}/artiste/{slug}` | `BandController::show()` |

In mono-locale mode the `/{_locale}` prefix is absent (see [i18n-routing.md](i18n-routing.md)).

## Entity notes

- `Album` extends `Product` (Sylius Core). The `band` ManyToOne relation is on `Product`, not on `Album` directly. Access it as `album.band` in Twig or `a.band` in DQL.
- `Album.artwork` is a OneToOne `AlbumImage` (AttachedImage). Use `aropixel_imagine_filter` — it handles null/missing images automatically (see [aropixel-imagine skill](../../.claude/skills/aropixel-imagine/SKILL.md)).
- `Band.image` is a OneToOne `BandImage` (AttachedImage). Same filter applies.
- Tracklists are ordered by `position ASC` via `#[ORM\OrderBy]` on the collection and enforced in the repository query.

## Gedmo translations (Band & Album)

Both `Band.description` and `Album.description` are translated via Gedmo Translatable (DoctrineExtensions). Unlike Sylius entities (which use `getTranslation()`), Gedmo entities require an explicit locale refresh in the controller:

```php
$band->setTranslatableLocale($request->getLocale());
$em->refresh($band);
```

Without this call, the description is always returned in the Gedmo fallback locale (`fr`). The refresh triggers Gedmo to reload the translated fields from `band_translation` / `album_translation` tables.

## Image filter sets

Defined in `config/packages/liip_imagine.yaml`:

| Filter | Dimensions | Mode | Used in |
|---|---|---|---|
| `album_card` | 400×400 | outbound (crop) | Album listing grid, homepage |
| `album_artwork` | 800×800 | inset (no crop) | Album detail page |
| `band_card` | 400×400 | outbound (crop) | Band listing, band show sidebar, homepage |

## Pagination

`AlbumController::index()` uses Pagerfanta with the Doctrine ORM adapter (`pagerfanta/doctrine-orm-adapter`):

```php
$pagerfanta = new Pagerfanta(new QueryAdapter($albumRepository->createOnlinePaginatedQuery($band)));
$pagerfanta->setMaxPerPage(12);
$pagerfanta->setCurrentPage(max(1, $request->query->getInt('page', 1)));
```

The `$albums` variable passed to the template is a `Pagerfanta` instance. Key properties available in Twig:
- `albums.currentPageResults` — iterable results for the current page
- `albums.haveToPaginate` — whether more than one page exists
- `albums.hasPreviousPage` / `albums.hasNextPage`
- `albums.previousPage` / `albums.nextPage`
- `albums.currentPage` / `albums.nbPages`

## Band filter on album listing

A `?band={slug}` query string parameter filters the album grid by Band. The select dropdown in `album/index.html.twig` uses a plain `onchange` redirect (no JavaScript controller needed). The `selectedBand` variable is `null` when no filter is active.

## Repository methods

### `BandRepository`

| Method | Returns | Notes |
|---|---|---|
| `findAllOnline()` | `Band[]` | `status = online`, ordered by name, eager-loads image |
| `findOneBySlug(string)` | `?Band` | Eager-loads image + members |

### `AlbumRepository`

| Method | Returns | Notes |
|---|---|---|
| `createOnlinePaginatedQuery(?Band)` | `QueryBuilder` | Used by Pagerfanta; optional band filter |
| `findLatestOnline(int, ?Band)` | `Album[]` | Used by homepage and band discography |
| `findOneBySlug(string)` | `?Album` | Eager-loads artwork, band, tracklists with tracks |
