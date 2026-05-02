# Audio previews — pipeline & WaveSurfer.js player

## Pipeline architecture

```
Admin: upload master (FLAC/MP3)
  └─► TrackMasterFileListener (postPersist / postUpdate)
        └─► dispatch EncodeTrackMp3Message → async transport
              └─► EncodeTrackMp3Handler
                    ├─ Read master from private.storage (files/{filename})
                    ├─ Encode MP3 128kbps CBR → write to previews.storage
                    ├─ Extract PCM 8kHz mono → compute 1,000 RMS windows → write JSON peaks to previews.storage
                    └─ Update Track.previewPath, Track.waveformPath, Track.duration
```

Run the worker in dev:

```bash
castor docker:builder -- php bin/console messenger:consume async --limit=1
```

---

## Storage

| File | Flysystem disk | Dev | Prod |
|------|---------------|-----|------|
| Master (uploaded FLAC/MP3) | `private.storage` | `private/files/{filename}` | S3 Cellar prefix `private` |
| MP3 preview 128kbps | `previews.storage` | `public/previews/{basename}.mp3` | S3 Cellar prefix `previews` |
| WaveSurfer JSON peaks | `previews.storage` | `public/previews/{basename}.peaks.json` | S3 Cellar prefix `previews` |

Files in `previews.storage` are written with `visibility = public` — direct HTTP access without signed URLs, natively compatible with HTTP Range Requests (scrubbing).

---

## JSON peaks format

```json
{
  "version": 2,
  "channels": 1,
  "sample_rate": 8000,
  "samples_per_pixel": 48,
  "bits": 8,
  "length": 1000,
  "data": [0.0, 0.12, 0.45, 0.78, ...]
}
```

- 1,000 RMS windows normalized between 0.0 and 1.0
- PCM signal extracted via `ffmpeg -f s16le -ac 1 -ar 8000`
- WaveSurfer.js v7 compatible: pass `peaks: [data]` (array of channels) in the constructor options

---

## `PreviewUrlResolver`

Injectable service `App\Service\PreviewUrlResolver`.

```php
$previewUrlResolver->getPreviewUrl($track);   // ?string — null if previewPath is absent
$previewUrlResolver->getWaveformUrl($track);  // ?string — null if waveformPath is absent
```

URL resolution based on environment (`PREVIEWS_BASE_URL` in `.env`):

| `PREVIEWS_BASE_URL` value | Returned URL |
|---------------------------|--------------|
| Empty (dev) | `/previews/{path}` |
| S3 URL (prod) | `{PREVIEWS_BASE_URL}/{path}` |

---

## WaveSurfer.js player (`audio_player_controller.js`)

Auto-discovered Stimulus controller, attached to each track `<li>` that has a preview.

### HTML attributes

```twig
<li data-controller="audio-player"
    data-audio-player-preview-url-value="{{ previewUrl }}"
    data-audio-player-waveform-url-value="{{ waveformUrl }}">
```

### Targets

| Target | Role |
|--------|------|
| `playIcon` | Play icon (hidden while playing) |
| `pauseIcon` | Pause icon (hidden while stopped) |
| `waveform` | WaveSurfer container — `opacity-0` until rendered |

### Behaviour

- **Lazy init**: WaveSurfer is only instantiated on the first click — no audio loaded on page render
- **Pre-fetched peaks**: the JSON peaks file is fetched before WaveSurfer is created; the waveform renders immediately, audio is only requested on `.play()`
- **Single active player**: a module-level `Set` pauses all other controller instances before starting playback
- **Turbo compatible**: `disconnect()` destroys the WaveSurfer instance on Turbo Drive navigation

---

## CORS configuration for Cellar (production)

To allow the player to load files cross-origin, add a CORS rule on the Cellar bucket via the Clever Cloud console:

```xml
<CORSConfiguration>
  <CORSRule>
    <AllowedOrigin>https://your-shop.com</AllowedOrigin>
    <AllowedMethod>GET</AllowedMethod>
    <AllowedHeader>*</AllowedHeader>
    <MaxAgeSeconds>3600</MaxAgeSeconds>
  </CORSRule>
</CORSConfiguration>
```

This rule applies to `previews.storage` (MP3 and JSON peaks). `private.storage` is not publicly exposed.

---

## Testing in development

1. Upload a master file (FLAC or MP3) on a Track from the admin
2. Consume the message:
   ```bash
   castor docker:builder -- php bin/console messenger:consume async --limit=1
   ```
3. Check in the database:
   - `Track.previewPath` = `{basename}.mp3`
   - `Track.waveformPath` = `{basename}.peaks.json`
   - `Track.duration` = `M:SS`
4. Check on disk: `application/public/previews/{basename}.mp3` and `.peaks.json` exist
5. Open the album page → click play → waveform visible, audio plays
