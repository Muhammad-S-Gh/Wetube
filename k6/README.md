# k6 upload verification

Start the stack and seed the deterministic per-VU accounts first:

```bash
docker compose up -d --build
docker compose exec app php artisan db:seed --class=K6TestUserSeeder
```

k6 reads `K6_BASE_URL` and writes its standard and custom metrics to the InfluxDB database configured by the root `.env`. Run the short, one-iteration verification scenarios with:

```bash
docker compose --profile test run --rm -e K6_SHORT=true k6 run /scripts/ffmpeg-upload-test.js
docker compose --profile test run --rm -e K6_SHORT=true k6 run /scripts/optimized-upload-test.js
```

Omit `K6_SHORT=true` to run the original ramping scenarios. Use `K6_MAX_VUS`, `K6_HOLD_DURATION`, and `K6_RAMP_DURATION` to tune those scenarios without editing the tests.
