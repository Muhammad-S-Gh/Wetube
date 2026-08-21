// Run ffmpeg-upload-test.js
docker compose --profile test run --rm k6 run /scripts/ffmpeg-upload-test.js

// Run optimized-upload-test.js
docker compose --profile test run --rm k6 run /scripts/optimized-upload-test.js
