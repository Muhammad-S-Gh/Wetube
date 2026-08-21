import http from 'k6/http';
import { check } from 'k6';
import { Counter, Rate } from 'k6/metrics';
import { BASE_URL, getFreshToken, loadTestEmail, login, thinkTime } from './lib/common.js';

// ─── Configuration ──────────────────────────────────────────
const shortRun = __ENV.K6_SHORT === 'true';
const maxVus = Number(__ENV.K6_MAX_VUS || (shortRun ? 1 : 20));
const hold = __ENV.K6_HOLD_DURATION || '3m';
const ramp = __ENV.K6_RAMP_DURATION || '45s';
const videoFixturePath = './sample_video.mp4';
const thumbFixturePath = './sample_thumb.jpg';

// ─── Load fixture files ──────────────────────────────────
const sampleVideo = open(videoFixturePath, 'b');
const sampleThumb = open(thumbFixturePath, 'b');

// ─── Custom metrics ──────────────────────────────────────
const optimizedSuccess = new Rate('optimized_upload_success');
const optimizedProcessingBlocked = new Counter('optimized_upload_processing_blocked');
const optimizedValidationErrors = new Counter('optimized_upload_validation_errors');
const optimizedServerErrors = new Counter('optimized_upload_server_errors');

// ─── Per‑VU login flag ─────────────────────────────────────
let loggedIn = false;

// ─── Test options ──────────────────────────────────────────
export const options = {
    scenarios: {
        optimized_upload: shortRun
            ? {
                  executor: 'per-vu-iterations',
                  vus: maxVus,
                  iterations: 1,
                  maxDuration: '3m',
              }
            : {
                  executor: 'ramping-vus',
                  startVUs: 0,
                  stages: [
                      { duration: ramp, target: Math.max(1, Math.floor(maxVus / 2)) },
                      { duration: hold, target: Math.max(1, Math.floor(maxVus / 2)) },
                      { duration: ramp, target: maxVus },
                      { duration: hold, target: maxVus },
                      { duration: ramp, target: 0 },
                  ],
                  gracefulRampDown: '20s',
              },
    },
    thresholds: {
        optimized_upload_success: ['rate>0.90'],
        http_req_duration: ['p(95)<15000'],
        http_req_failed: ['rate<0.10'],
        optimized_upload_server_errors: ['count<10'],
    },
    tags: { test: 'optimized-store' },
    discardResponseBodies: false,
};

// ─── Main iteration ──────────────────────────────────────
export default function () {
    // ─── Login only once per VU ────────────────────────────────
    if (!loggedIn) {
        const email = loadTestEmail(); // uses __VU automatically
        login(email); // HTTP request – allowed here
        loggedIn = true;
    }

    const csrfToken = getFreshToken();
    if (!csrfToken) {
        thinkTime(2, 4);
        return;
    }

    const payload = {
        video: http.file(sampleVideo, `optimized_${__VU}_${__ITER}.mp4`, 'video/mp4'),
        image: http.file(sampleThumb, `optimized_${__VU}_${__ITER}.jpg`, 'image/jpeg'),
        title: `Optimized load test ${__VU}-${__ITER}-${Date.now()}`,
        description: 'Optimized direct Cloudinary upload test',
        _token: csrfToken,
    };

    const res = http.post(`${BASE_URL}/videos/optimized`, payload, {
        timeout: '120s',
        tags: { endpoint: 'videos.optimizedStore' },
    });

    const ok = res.status >= 200 && res.status < 400;
    const blockedByProcessing = res.body && String(res.body).includes('already_have_video_processing');
    const validationFailed = res.status === 422 || res.status === 419;
    const serverFailed = res.status >= 500;

    if (blockedByProcessing) optimizedProcessingBlocked.add(1);
    if (validationFailed) optimizedValidationErrors.add(1);
    if (serverFailed) optimizedServerErrors.add(1);

    check(res, {
        'optimized upload 2xx/3xx': () => ok,
        'optimized upload under 120s': () => res.timings.duration < 120000,
        'not blocked by processing lock': () => !blockedByProcessing,
        'no server error': () => !serverFailed,
    });

    optimizedSuccess.add(ok);

    if (!ok) {
        console.error(
            JSON.stringify({
                vu: __VU,
                iter: __ITER,
                status: res.status,
                duration: res.timings.duration,
                blockedByProcessing,
                body: String(res.body).slice(0, 300),
            }),
        );
    }

    thinkTime(2, 4);
}

// ─── Summary output ──────────────────────────────────────
export function handleSummary(data) {
    return {
        stdout:
            JSON.stringify(
                {
                    route: '/videos/optimized',
                    maxVus,
                    videoFixturePath,
                    thumbFixturePath,
                    successRate: data.metrics.optimized_upload_success?.values?.rate,
                    processingBlocked: data.metrics.optimized_upload_processing_blocked?.values?.count,
                    validationErrors: data.metrics.optimized_upload_validation_errors?.values?.count,
                    serverErrors: data.metrics.optimized_upload_server_errors?.values?.count,
                    p95Ms: data.metrics.http_req_duration?.values?.['p(95)'],
                    httpReqFailedRate: data.metrics.http_req_failed?.values?.rate,
                    checksRate: data.metrics.checks?.values?.rate,
                },
                null,
                2,
            ) + '\n',
    };
}
