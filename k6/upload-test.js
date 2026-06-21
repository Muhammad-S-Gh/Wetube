import http from 'k6/http';
import { check } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';
import {
  BASE_URL,
  PROMETHEUS_URL,
  getFreshToken,
  loadTestEmail,
  login,
  thinkTime,
} from './lib/common.js';

const maxVus = Number(__ENV.LEGACY_MAX_VUS || __ENV.MAX_VUS || 20);
const hold = __ENV.HOLD_DURATION || '3m';
const ramp = __ENV.RAMP_DURATION || '45s';
const videoFixturePath = __ENV.VIDEO_FIXTURE || './sample_video.mp4';
const thumbFixturePath = __ENV.THUMB_FIXTURE || './sample_thumb.jpg';
const sampleVideo = open(videoFixturePath, 'b');
const sampleThumb = open(thumbFixturePath, 'b');

const uploadAccepted = new Rate('legacy_upload_accepted');
const uploadDuration = new Trend('legacy_upload_duration', true);
const uploadRejected = new Counter('legacy_upload_rejected');
const processingBlocked = new Counter('legacy_upload_processing_blocked');
const validationErrors = new Counter('legacy_upload_validation_errors');
const serverErrors = new Counter('legacy_upload_server_errors');

/**
 * Legacy videos.store stress test.
 *
 * Goals:
 * - Measure how fast Laravel accepts uploads (before FFmpeg/queue work).
 * - Avoid the old failure mode: one shared user stuck in "processing".
 * - Keep concurrency modest because each request still ships full multipart payloads.
 */
export const options = {
  scenarios: {
    legacy_acceptance: {
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
    legacy_upload_accepted: ['rate>0.85'],
    legacy_upload_duration: ['p(95)<30000'],
    http_req_failed: ['rate<0.20'],
    legacy_upload_server_errors: ['count<10'],
  },
  tags: { test: 'legacy-store' },
};

export function setup() {
  login(loadTestEmail(1));
  return {};
}

export default function () {
  const email = loadTestEmail(__VU);
  login(email);

  const csrfToken = getFreshToken();
  if (!csrfToken) {
    uploadRejected.add(1);
    thinkTime(1, 2);
    return;
  }

  const payload = {
    video: http.file(sampleVideo, `legacy_${__VU}_${__ITER}.mp4`, 'video/mp4'),
    image: http.file(sampleThumb, `legacy_${__VU}_${__ITER}.jpg`, 'image/jpeg'),
    title: `Legacy load test ${__VU}-${__ITER}-${Date.now()}`,
    description: 'Legacy store acceptance test',
    _token: csrfToken,
  };

  const res = http.post(`${BASE_URL}/videos`, payload, {
    timeout: '90s',
    tags: { endpoint: 'videos.store' },
  });

  const accepted = res.status >= 200 && res.status < 400;
  const blockedByProcessing = res.body && String(res.body).includes('already_have_video_processing');
  const validationFailed = res.status === 422 || res.status === 419;
  const serverFailed = res.status >= 500;

  if (blockedByProcessing) {
    processingBlocked.add(1);
  }

  if (validationFailed) {
    validationErrors.add(1);
  }

  if (serverFailed) {
    serverErrors.add(1);
  }

  check(res, {
    'legacy upload accepted (2xx/3xx)': () => accepted,
    'not blocked by processing lock': () => !blockedByProcessing,
    'no server error': () => !serverFailed,
  });

  uploadAccepted.add(accepted);
  uploadDuration.add(res.timings.duration);

  if (!accepted) {
    uploadRejected.add(1);
    console.error(
      JSON.stringify({
        route: '/videos',
        vu: __VU,
        iter: __ITER,
        status: res.status,
        duration: res.timings.duration,
        blockedByProcessing,
        body: String(res.body).slice(0, 300),
      }),
    );
  }

  // Give the server breathing room — legacy path still writes temp files + dispatches jobs.
  thinkTime(4, 8);
}

export function handleSummary(data) {
  return {
    stdout: JSON.stringify({
      route: '/videos',
      maxVus,
      videoFixturePath,
      thumbFixturePath,
      acceptedRate: data.metrics.legacy_upload_accepted?.values?.rate,
      rejected: data.metrics.legacy_upload_rejected?.values?.count,
      processingBlocked: data.metrics.legacy_upload_processing_blocked?.values?.count,
      validationErrors: data.metrics.legacy_upload_validation_errors?.values?.count,
      serverErrors: data.metrics.legacy_upload_server_errors?.values?.count,
      p95Ms: data.metrics.legacy_upload_duration?.values?.['p(95)'],
      httpReqFailedRate: data.metrics.http_req_failed?.values?.rate,
      checksRate: data.metrics.checks?.values?.rate,
    }, null, 2) + '\n',
  };
}

export const teardown = () => {};

// k6 experimental Prometheus remote write
export const cloud = {
  projectID: Number(__ENV.K6_CLOUD_PROJECT_ID || 0),
  name: 'wetube-legacy-upload',
};

export const ext = {
  loadimpact: {
    projectID: cloud.projectID,
    name: cloud.name,
  },
};

if (__ENV.K6_PROMETHEUS_RW === '1') {
  options.ext = {
    ...(options.ext || {}),
  };
}

options.discardResponseBodies = false;

// Enable via: K6_PROMETHEUS_RW=1 K6_OUT=experimental-prometheus-rw
if (__ENV.K6_OUT === 'experimental-prometheus-rw') {
  options.systemTags = ['vu', 'iter', 'scenario', 'endpoint', 'test'];
}

void PROMETHEUS_URL;
