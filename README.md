# Wetube

<p align="center">
  <strong>A video platform engineered around asynchronous processing, queue-based workloads, and observable performance.</strong>
</p>

<p align="center">
  Laravel 12 · PHP 8.2+ · MySQL · Redis · FFmpeg · Cloudinary · Docker · k6 · InfluxDB · Prometheus · Grafana
</p>

---

## Overview

Wetube is a YouTube-inspired video platform built to investigate a specific backend engineering problem:

**How should a video-processing system handle expensive media operations without allowing them to dominate the HTTP request lifecycle?**

The project implements two upload pipelines:

- **Legacy pipeline** — `POST /videos`
- **Optimized pipeline** — `POST /videos/optimized`

Both pipelines accept the same type of upload, but the optimized pipeline is designed around asynchronous processing and background jobs.

The project therefore focuses less on the social-platform features themselves and more on the engineering behind a workload involving:

- large multipart uploads
- video processing with FFmpeg
- asynchronous jobs
- Redis-backed queues
- cloud media storage
- concurrent traffic
- performance measurement
- application and infrastructure observability

---

## Engineering Focus

The central architectural difference is the treatment of expensive work.

### Legacy pipeline

```text
Client
  │
  ▼
Nginx
  │
  ▼
Laravel
  │
  ├── Upload handling
  ├── Processing work
  └── External media operations
  │
  ▼
Response
```

### Optimized pipeline

```text
Client
  │
  ▼
Nginx
  │
  ▼
Laravel
  │
  ├── Validate request
  ├── Store temporary data
  └── Dispatch background jobs
           │
           ▼
      Redis Queue
           │
           ▼
      Queue Workers
        │       │
        │       ├── FFmpeg / media processing
        │       ├── Moderation / sanitization
        │       └── Cloudinary operations
        │
        ▼
   Finalize video
```

The optimized architecture separates **request acceptance** from **expensive background processing**, allowing the application to remain responsive while media work continues asynchronously.

---

# Architecture

```text
                              ┌─────────────────┐
                              │     Browser     │
                              └────────┬────────┘
                                       │
                                       ▼
                              ┌─────────────────┐
                              │      Nginx      │
                              │      :8000      │
                              └────────┬────────┘
                                       │
                                       ▼
                         ┌────────────────────────────┐
                         │       Laravel / PHP        │
                         │                            │
                         │ Controllers • Services     │
                         │ Validation • Auth • API    │
                         └───────────┬───────┬────────┘
                                     │       │
                          ┌──────────▼───┐ ┌─▼──────────┐
                          │    MySQL      │ │    Redis    │
                          │    :3306      │ │    :6379    │
                          └───────────────┘ └─────┬───────┘
                                                  │
                                                  ▼
                                         ┌────────────────┐
                                         │ Queue Workers  │
                                         └───────┬────────┘
                                                 │
                              ┌──────────────────┼──────────────────┐
                              │                  │                  │
                              ▼                  ▼                  ▼
                           FFmpeg           Cloudinary       Processing Jobs


        ┌──────────────────────────────────────────────────────────┐
        │                       Observability                       │
        │                                                          │
        │   k6 ───────────► InfluxDB ───────────────► Grafana     │
        │                                                          │
        │   Laravel ──────► Prometheus ──────────────► Grafana    │
        │                         ▲                                │
        │                         ├── cAdvisor                    │
        │                         ├── MySQL Exporter               │
        │                         └── Redis Exporter               │
        └──────────────────────────────────────────────────────────┘
```

### Observability model

Wetube deliberately separates load-test telemetry from application telemetry.

| System             | Responsibility                              |
| ------------------ | ------------------------------------------- |
| **k6**             | Generates controlled HTTP load              |
| **InfluxDB**       | Stores k6 test metrics                      |
| **Prometheus**     | Collects Laravel and infrastructure metrics |
| **Grafana**        | Visualizes both sources                     |
| **cAdvisor**       | Container-level resource metrics            |
| **MySQL Exporter** | MySQL metrics                               |
| **Redis Exporter** | Redis metrics                               |

This separation makes it possible to observe both **what the client experienced** and **what the system was doing internally** during the same workload.

---

# Features

Wetube includes:

- Video upload and management
- Asynchronous video processing
- FFmpeg-based processing
- Cloudinary media storage
- Redis queues
- Background workers
- Upload status tracking
- Authentication and user channels
- Likes and comments
- Reports and notifications
- Watch history
- Dockerized local infrastructure
- Automated backend testing
- k6 load-testing scenarios
- Prometheus metrics
- Grafana dashboards
- MySQL, Redis, and container monitoring

---

# Technology Stack

| Technology     | Role                                    |
| -------------- | --------------------------------------- |
| Laravel 12     | Application framework                   |
| PHP 8.2+       | Backend runtime                         |
| MySQL          | Relational persistence                  |
| Redis          | Queues and caching                      |
| FFmpeg         | Video processing                        |
| Cloudinary     | Media storage and delivery              |
| Docker Compose | Service orchestration                   |
| k6             | Load generation and performance testing |
| InfluxDB       | k6 metric storage                       |
| Prometheus     | Application and infrastructure metrics  |
| Grafana        | Observability and visualization         |
| cAdvisor       | Container monitoring                    |
| Pest           | Automated testing                       |
| Livewire       | Interactive UI                          |
| Jetstream      | Authentication                          |
| Sanctum        | API authentication                      |
| Vite           | Asset build pipeline                    |
| Tailwind CSS   | Frontend styling                        |

---

# Project Structure

```text
Wetube/
├── backend/
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── tests/
│   ├── composer.json
│   └── package.json
│
├── k6/
│   ├── lib/
│   ├── ffmpeg-upload-test.js
│   ├── optimized-upload-test.js
│   ├── generate-fixtures.sh
│   └── README.md
│
├── docker/
│   ├── grafana/
│   ├── nginx/
│   ├── php/
│   ├── prometheus/
│   ├── redis/
│   └── mysql-exporter/
│
├── docker-compose.yml
├── .env.example
└── README.md
```

---

# Application Endpoints

The performance study is centered around the two upload endpoints:

| Pipeline  | Endpoint                     | Purpose                     |
| --------- | ---------------------------- | --------------------------- |
| Legacy    | `POST /videos`               | Traditional upload flow     |
| Optimized | `POST /videos/optimized`     | Asynchronous upload flow    |
| Status    | `GET /videos/{video}/status` | Processing-state visibility |

The remainder of the application provides the surrounding platform functionality required to create, manage, and consume videos.

---

# Performance Testing

## Objective

The k6 suite evaluates the behavior of the two upload pipelines under concurrent traffic.

The comparison focuses on:

- request latency
- successful upload acceptance
- failed requests
- server errors
- validation failures
- processing conflicts
- system checks
- iteration behavior

The optimized pipeline is expected to reduce the amount of expensive work performed during the request itself.

The benchmark therefore evaluates **request responsiveness**, rather than attempting to claim that asynchronous processing makes the actual media-processing work disappear.

---

## Test Scenarios

### Legacy upload

File:

```text
k6/ffmpeg-upload-test.js
```

Endpoint:

```text
POST /videos
```

Default workload:

```text
Maximum VUs: 20
Ramp:        45s
Hold:        3m
```

The test measures:

- upload acceptance rate
- request duration
- processing conflicts
- validation failures
- server errors

---

### Optimized upload

File:

```text
k6/optimized-upload-test.js
```

Endpoint:

```text
POST /videos/optimized
```

Default workload:

```text
Maximum VUs: 20
Ramp:        45s
Hold:        3m
```

The test measures:

- upload success rate
- request duration
- processing conflicts
- validation failures
- server errors

The scenario can be parameterized without modifying the test source.

---

# Running the Performance Suite

The benchmark stack consists of:

```text
Laravel + Nginx + MySQL + Redis + Queue Workers
                         │
                         ▼
                        k6
                         │
                         ▼
                     InfluxDB
                         │
                         ▼
                      Grafana
```

Start the application and monitoring stack:

```bash
docker compose up -d --build
```

Create the deterministic load-test accounts:

```bash
docker compose exec app php artisan db:seed --class=K6TestUserSeeder
```

The scenarios use users such as:

```text
loadtest1@example.com
loadtest2@example.com
...
```

---

## Smoke Test

Before a full benchmark, the optimized route can be verified with a minimal one-iteration scenario:

```bash
docker compose --profile test run --rm \
  -e K6_SHORT=true \
  k6 run /scripts/optimized-upload-test.js
```

A successful smoke test confirms that:

- the test can authenticate
- a CSRF token is obtained
- the upload endpoint is reachable
- the multipart request is accepted
- the request does not fail because of a server error
- the scenario reaches its configured thresholds

The legacy scenario can be verified in the same way:

```bash
docker compose --profile test run --rm \
  -e K6_SHORT=true \
  k6 run /scripts/ffmpeg-upload-test.js
```

---

# Full Benchmark

For a direct comparison, both pipelines should be executed with the **same workload parameters**.

Example:

```bash
docker compose --profile test run --rm \
  -e K6_MAX_VUS=20 \
  -e K6_HOLD_DURATION=3m \
  -e K6_RAMP_DURATION=45s \
  k6 run /scripts/ffmpeg-upload-test.js
```

Then:

```bash
docker compose --profile test run --rm \
  -e K6_MAX_VUS=20 \
  -e K6_HOLD_DURATION=3m \
  -e K6_RAMP_DURATION=45s \
  k6 run /scripts/optimized-upload-test.js
```

Using identical load parameters avoids comparing two different workloads.

---

# k6 Results

Each scenario emits a compact JSON summary at the end of the run.

The summary contains the values most useful for pipeline comparison:

| Metric                    | Legacy | Optimized |
| ------------------------- | -----: | --------: |
| P95 duration              |      — |         — |
| Acceptance / success rate |      — |         — |
| HTTP failed rate          |      — |         — |
| Checks rate               |      — |         — |
| Server errors             |      — |         — |
| Validation errors         |      — |         — |
| Processing blocked        |      — |         — |

The most important latency measurement is **P95 request duration**.

### Comparison

```text
Improvement % =
((Legacy P95 - Optimized P95) / Legacy P95) × 100
```

Only measured benchmark values should be published in the project documentation.

No performance number is considered valid unless it comes from an actual recorded run.

---

# Observing the Tests in Grafana

Grafana:

```text
http://localhost:3000
```

Wetube provisions a dedicated dashboard:

```text
Wetube Video Upload Load Tests
```

The dashboard is backed by **InfluxDB**, which receives the k6 metrics.

It exposes the standard k6 signals together with Wetube-specific metrics.

### Standard k6 metrics

Examples include:

- HTTP request duration
- HTTP request rate
- active VUs
- maximum VUs
- failed HTTP requests
- successful checks
- iteration duration
- dropped iterations

### Legacy metrics

```text
legacy_upload_accepted
legacy_upload_processing_blocked
legacy_upload_validation_errors
legacy_upload_server_errors
```

### Optimized metrics

```text
optimized_upload_success
optimized_upload_processing_blocked
optimized_upload_validation_errors
optimized_upload_server_errors
```

---

## Reading Grafana During a Test

When a k6 test is running:

```text
k6
 │
 └──► InfluxDB
          │
          └──► Grafana
```

Open the **Wetube Video Upload Load Tests** dashboard and select the time range covering the test run.

The most useful panels for a technical comparison are:

### 1. Request duration

Look at the P95 latency of the legacy and optimized runs.

This answers:

> How quickly does the API respond under load?

### 2. Success / acceptance rate

This answers:

> Does the system continue accepting uploads successfully as concurrency increases?

### 3. Error metrics

Inspect:

- server errors
- validation failures
- processing conflicts

This answers:

> Does the optimized architecture introduce or eliminate failure modes under load?

### 4. Infrastructure metrics

Switch to the application/infrastructure dashboards to correlate the test with:

- Redis activity
- MySQL activity
- container resource consumption
- application metrics
- queue behavior

The result is a complete picture:

```text
Client experience
      +
Application behavior
      +
Queue behavior
      +
Infrastructure behavior
```

---

# Application Metrics

Prometheus collects application and infrastructure telemetry separately from k6.

Prometheus:

```text
http://localhost:9090
```

Laravel exposes:

```text
/metrics
```

The stack also includes:

- cAdvisor
- MySQL Exporter
- Redis Exporter

Grafana can therefore be used to correlate application-level measurements with infrastructure behavior during the load test.

---

# Reproducibility

The repository keeps the performance scenarios and their fixtures under version control.

The test inputs are therefore deterministic enough to reproduce the benchmark environment locally.

The configurable parameters are:

```text
K6_MAX_VUS
K6_HOLD_DURATION
K6_RAMP_DURATION
K6_BASE_URL
```

This allows experiments to be repeated without editing the test implementation.

---

# Backend Testing

The Laravel test suite can be executed with:

```bash
docker compose exec app php artisan test
```

The project uses Pest/Laravel testing tooling for backend verification.

---

# Security

Environment-specific credentials are intentionally excluded from version control.

Create local environment files from:

```text
.env.example
backend/.env.example
```

Production credentials, API keys, storage credentials, and SMTP credentials must be supplied through the deployment environment rather than committed to the repository.

---

# Project Outcome

Wetube is a completed engineering case study focused on one problem:

**Separating expensive video-processing workloads from the HTTP request lifecycle while making the resulting system measurable under load.**

The project demonstrates a complete path from:

```text
HTTP request
     ↓
Laravel
     ↓
Redis queue
     ↓
Background workers
     ↓
FFmpeg / Cloudinary
```

with performance measurement through:

```text
k6
 ↓
InfluxDB
 ↓
Grafana
```

and application/infrastructure observability through:

```text
Laravel + Exporters
 ↓
Prometheus
 ↓
Grafana
```

The project is considered complete and is preserved as a portfolio reference and technical case study.

---

# Author

**Muhammad-S-Gh**

[GitHub](https://github.com/Muhammad-S-Gh)
