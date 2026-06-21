# Wetube k6 load tests

## 1. Fixtures

```bash
chmod +x k6/generate-fixtures.sh
./k6/generate-fixtures.sh
```

## 2. Seed load-test users (one per VU)

```bash
docker compose exec app php artisan db:seed --class=LoadTestUserSeeder
```

Users: `loadtest1@example.com` … `loadtest100@example.com` (password: `1234567890`).

## 3. Start observability stack

```bash
docker compose up -d prometheus grafana nginx app db redis
```

- Prometheus: http://localhost:9090
- Grafana: http://localhost:3000 (dashboard: **Wetube Video Upload Load Tests**)

## 4. Run tests with Prometheus remote write

Ensure the **queue worker** is running — optimized uploads finish in background jobs:

```bash
docker compose up -d queue
```

### Legacy `videos.store` (acceptance-focused, default max 20 VUs)

```bash
docker compose run --rm k6 run \
  -o experimental-prometheus-rw=http://prometheus:9090/api/v1/write \
  --tag test=legacy-store \
  /scripts/upload-test.js
```

### Optimized `videos.optimizedStore` (default max 50 VUs)

```bash
docker compose run --rm k6 run \
  -o experimental-prometheus-rw=http://prometheus:9090/api/v1/write \
  --tag test=optimized-store \
  /scripts/optimized-upload-test.js
```

Stress level and fixture files can be changed per run:

```bash
MAX_VUS=100 HOLD_DURATION=5m RAMP_DURATION=1m docker compose run --rm k6 run /scripts/optimized-upload-test.js
```

The scripts read `/scripts/sample_video.mp4` and `/scripts/sample_thumb.jpg` by default. Override with `VIDEO_FIXTURE` and `THUMB_FIXTURE` when you want larger media files mounted into the k6 container.

## Notes

- The legacy test uses **per-VU users** so uploads are not blocked by the `already_have_video_processing` guard.
- Legacy concurrency stays low because each request still uploads full multipart payloads and dispatches FFmpeg jobs.
- The optimized route accepts the upload, runs **text moderation synchronously**, stores files in temp storage, then queues:
  1. `OptimizedSanitizeJob` — thumbnail + video moderation (nothing goes to Cloudinary yet)
  2. `OptimizedCloudinaryUploadJob` — streams approved files to Cloudinary and finalizes the video
- Failures create a **bell notification** (no browser alert popups).
