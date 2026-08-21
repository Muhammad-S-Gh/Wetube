# 🎬 Wetube

<p align="center">
  <strong>A Laravel-powered YouTube-inspired video platform built around asynchronous video processing, cloud media storage, and performance observability.</strong>
</p>

<p align="center">
  Laravel 12 • PHP 8.2+ • FFmpeg • Cloudinary • Docker • k6 • Prometheus • Grafana
</p>

---

## 📖 About

**Wetube** is a YouTube-inspired video platform built with Laravel and designed to explore the engineering challenges behind modern video applications.

The project goes beyond basic CRUD by combining **video uploads, asynchronous processing, FFmpeg, Cloudinary, Redis queues, Docker, load testing, and observability** into one application.

A major focus is the video-processing pipeline and its behavior under load. Wetube includes both a traditional upload flow and an optimized upload flow that moves expensive work into background jobs so the API can accept uploads quickly while processing continues asynchronously.

> 🚧 **Project status:** Active development. Core application and infrastructure are implemented; performance scenarios and further polishing are ongoing.

---

## ✨ Highlights

- 🎥 Video upload and management
- ⚡ Optimized asynchronous video-processing pipeline
- 🧰 FFmpeg-powered video processing
- ☁️ Cloudinary media storage and delivery
- 🔐 Laravel Sanctum authentication
- 👤 User channels and dashboards
- ❤️ Likes and 💬 comments
- 🚨 Video reporting and notifications
- 🕘 Watch history
- 🔄 Video upload-status tracking
- 🧵 Redis-backed queues and background jobs
- 🐳 Fully containerized development environment
- 📈 k6 load testing
- 📊 Prometheus metrics
- 📉 Grafana dashboards
- 🖥️ cAdvisor, MySQL Exporter, and Redis Exporter monitoring

---

## 🏗️ Architecture

```text
                         ┌──────────────────┐
                         │      Browser     │
                         └────────┬─────────┘
                                  │
                                  ▼
                         ┌──────────────────┐
                         │      Nginx       │
                         │      :8000       │
                         └────────┬─────────┘
                                  │
                                  ▼
                    ┌──────────────────────────┐
                    │      Laravel / PHP       │
                    │ Controllers • Services   │
                    │ Auth • Validation • API  │
                    └───────┬──────────┬────────┘
                            │          │
                   ┌────────▼───┐  ┌──▼──────────┐
                   │    MySQL    │  │    Redis    │
                   │   :3306     │  │    :6379    │
                   └─────────────┘  └──────┬──────┘
                                           │
                                           ▼
                                  ┌─────────────────┐
                                  │ Queue Workers   │
                                  └────────┬────────┘
                                           │
                         ┌─────────────────┼──────────────────┐
                         ▼                 ▼                  ▼
                    ┌─────────┐      ┌──────────┐       ┌────────────┐
                    │ FFmpeg  │      │Cloudinary│       │ Video Jobs │
                    └─────────┘      └──────────┘       └────────────┘

     ┌─────────────────────────────────────────────────────────────┐
     │                     Observability                           │
     │  k6 ──► Prometheus ──► Grafana                              │
     │          ▲          │                                      │
     │          │          ├── cAdvisor                           │
     │          │          ├── MySQL Exporter                      │
     │          │          └── Redis Exporter                      │
     └─────────────────────────────────────────────────────────────┘
```

### Video processing pipeline

Wetube provides two upload paths:

- **Legacy:** `POST /videos`
- **Optimized:** `POST /videos/optimized`

The optimized path is designed to keep expensive media work out of the request lifecycle:

```text
Upload request
     │
     ▼
Laravel accepts upload
     │
     ▼
Temporary storage
     │
     ▼
Queue background jobs
     │
     ├──► Sanitization / moderation
     │
     └──► Cloudinary upload
              │
              ▼
        Finalize video
```

---

## 🧰 Tech Stack

| Technology | Purpose |
|---|---|
| **Laravel 12** | Backend framework and application architecture |
| **PHP 8.2+** | Server-side runtime |
| **Livewire** | Interactive server-driven UI |
| **Laravel Jetstream** | Authentication and account features |
| **Laravel Sanctum** | API authentication |
| **MySQL** | Relational database |
| **Redis** | Queues and caching |
| **FFmpeg** | Video processing and media conversion |
| **Cloudinary** | Cloud media storage and delivery |
| **Docker Compose** | Local development and service orchestration |
| **k6** | Load and performance testing |
| **Prometheus** | Metrics collection |
| **Grafana** | Metrics visualization and dashboards |
| **cAdvisor** | Container resource metrics |
| **MySQL Exporter** | MySQL metrics for Prometheus |
| **Redis Exporter** | Redis metrics for Prometheus |
| **Vite** | Frontend asset bundling |
| **Tailwind CSS** | UI styling |
| **Pest** | Automated testing |

---

## 📁 Project Structure

```text
Wetube/
├── backend/                       # Laravel application
│   ├── app/
│   │   ├── Http/
│   │   ├── Jobs/Video/            # Asynchronous video-processing jobs
│   │   ├── Services/              # Application/business services
│   │   └── ...
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── tests/
│   ├── composer.json
│   └── package.json
│
├── k6/                            # Performance/load tests
│   ├── lib/                        # Shared test helpers and fixtures
│   ├── upload-test.js              # Legacy upload load test
│   ├── optimized-upload-test.js    # Optimized upload load test
│   └── README.md
│
├── docker/
│   ├── grafana/                   # Dashboards + provisioning
│   ├── nginx/                     # Nginx configuration
│   ├── php/                       # PHP image/configuration
│   ├── prometheus/                # Prometheus configuration
│   ├── redis/                     # Redis configuration
│   └── mysql-exporter/            # MySQL exporter configuration
│
├── docker-compose.yml
├── .env.example
└── README.md
```

---

## 🚀 Getting Started

### Prerequisites

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- Git

For a non-Docker Laravel workflow, you will additionally need PHP, Composer, Node.js/npm, MySQL, Redis, and FFmpeg.

### 1. Clone the repository

```bash
git clone https://github.com/Muhammad-S-Gh/Wetube.git
cd Wetube
```

### 2. Configure environment variables

```bash
cp .env.example .env
```

Fill in the required database, Grafana, Cloudinary, and image-version settings for your environment.

> 🔒 Never commit real credentials or secrets.

### 3. Start Docker

```bash
docker compose up -d --build
```

Check services:

```bash
docker compose ps
```

### 4. Prepare Laravel

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 5. Open Wetube

**http://localhost:8000**

---

## 🐳 Docker Services

| Service | Port | Role |
|---|---:|---|
| `nginx` | `8000` | Web entry point |
| `app` | — | Laravel/PHP application |
| `db` | `3306` | MySQL |
| `redis` | `6379` | Queue/cache backend |
| `queue` | — | Background job worker |
| `scheduler` | — | Scheduled tasks |
| `k6` | — | Load testing |
| `prometheus` | `9090` | Metrics collection |
| `grafana` | `3000` | Metrics dashboards |
| `cadvisor` | `8080` | Container metrics |
| `mysql-exporter` | `9104` | MySQL metrics |
| `redis-exporter` | `9121` | Redis metrics |

Useful commands:

```bash
# Start everything
docker compose up -d

# Rebuild containers
docker compose up -d --build

# Stop services
docker compose down

# Application logs
docker compose logs -f app

# Queue logs
docker compose logs -f queue

# Open a Laravel shell
docker compose exec app bash
```

---

## 🎥 Video Uploads

### Legacy upload

```http
POST /videos
```

The traditional upload path accepts the multipart upload and dispatches video-processing work.

### Optimized upload

```http
POST /videos/optimized
```

The optimized workflow accepts the upload, stores temporary files, and delegates expensive processing to background jobs.

The processing pipeline includes sanitization/moderation followed by Cloudinary upload and finalization.

### Upload status

```http
GET /videos/{video}/status
```

This endpoint exposes the state of an uploaded video while background processing is taking place.

### Video management

```http
GET    /videos
POST   /videos
POST   /videos/optimized
GET    /videos/{video}
PUT    /videos/{video}
DELETE /videos/{video}
```

Authenticated users can also interact with videos through likes, comments, reports, and notifications.

---

## 🔐 Authentication & Features

Authenticated users can access:

- Dashboard
- Channels
- Watch history
- Video management
- Likes
- Comments
- Reports
- Notifications
- Video upload status

The application uses Laravel Jetstream for account/authentication functionality and Sanctum for API authentication.

---

## 🧪 Testing

Wetube uses Pest/Laravel testing tooling.

From `backend/`:

```bash
php artisan test
```

Or with Docker:

```bash
docker compose exec app php artisan test
```

---

## 📈 Performance Testing with k6

Wetube includes dedicated k6 scenarios for video-upload load testing. The suite compares the legacy and optimized upload pipelines and exports metrics to Prometheus.

### Generate test fixtures

```bash
chmod +x k6/generate-fixtures.sh
./k6/generate-fixtures.sh
```

Default fixtures:

```text
k6/sample_video.mp4
k6/sample_thumb.jpg
```

Override them with `VIDEO_FIXTURE` and `THUMB_FIXTURE` when required.

### Seed load-test users

```bash
docker compose exec app php artisan db:seed --class=LoadTestUserSeeder
```

The setup provisions per-VU users such as:

```text
loadtest1@example.com
loadtest2@example.com
...
```

### Start the stack

```bash
docker compose up -d prometheus grafana nginx app db redis queue
```

### Legacy upload test

```bash
docker compose run --rm k6 run \
  -o experimental-prometheus-rw=http://prometheus:9090/api/v1/write \
  --tag test=legacy-store \
  /scripts/upload-test.js
```

### Optimized upload test

```bash
docker compose run --rm k6 run \
  -o experimental-prometheus-rw=http://prometheus:9090/api/v1/write \
  --tag test=optimized-store \
  /scripts/optimized-upload-test.js
```

### Adjust load

```bash
MAX_VUS=100 HOLD_DURATION=5m RAMP_DURATION=1m \
docker compose run --rm k6 run /scripts/optimized-upload-test.js
```

The legacy scenario intentionally uses lower concurrency because each request sends a complete multipart payload and still triggers the heavier processing path.

For more detail, see [`k6/README.md`](k6/README.md).

---

## 📊 Observability

Wetube treats performance testing as an observable system rather than simply a pass/fail benchmark.

```text
                 ┌───────────┐
                 │    k6     │
                 └─────┬─────┘
                       │ Remote Write
                       ▼
                ┌─────────────┐
                │ Prometheus  │
                └──────┬──────┘
                       │
                       ▼
                ┌─────────────┐
                │   Grafana   │
                └─────────────┘

 Infrastructure ──► cAdvisor
                ├─► MySQL Exporter
                └─► Redis Exporter
                         │
                         ▼
                     Prometheus
```

### Prometheus

**http://localhost:9090**

Prometheus receives k6 remote-write metrics and scrapes infrastructure exporters.

### Grafana

**http://localhost:3000**

The repository includes Grafana provisioning and a dedicated **Wetube Video Upload Load Tests** dashboard.

### Exporters

The monitoring stack includes metrics for:

- Docker containers through cAdvisor
- MySQL through `mysqld-exporter`
- Redis through `redis_exporter`

This makes it possible to correlate API/load-test behavior with infrastructure utilization.

---

## 🎯 Performance Testing Goals

The k6 suite is intended to answer questions such as:

- How quickly can the API accept video uploads?
- How does the legacy pipeline behave as concurrency increases?
- Does the optimized pipeline keep request latency under control?
- How many uploads fail or are rejected under load?
- What happens to queue workers as traffic increases?
- How do MySQL, Redis, and container resources behave during a test?

Custom metrics include upload acceptance, upload duration, validation failures, processing conflicts, and server errors.

> 📌 **Benchmark results:** Final benchmark numbers will be added after the performance-test suite is fully polished and repeatable.

---

## 🗺️ Roadmap

- [x] Laravel video-platform foundation
- [x] Authentication and user channels
- [x] Video upload workflow
- [x] FFmpeg processing
- [x] Cloudinary integration
- [x] Redis queue workers
- [x] Optimized asynchronous upload pipeline
- [x] Dockerized infrastructure
- [x] Prometheus + Grafana observability
- [x] k6 upload load tests
- [ ] Finalize upload/update performance scenarios
- [ ] Add reproducible benchmark results
- [ ] Expand automated API/integration coverage
- [ ] Continue UI/UX polishing

---

## 🛠️ Useful Commands

From `backend/`:

```bash
composer install
npm install
php artisan test
php artisan optimize:clear
npm run dev
npm run build
```

With Docker:

```bash
docker compose exec app bash
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose ps
```

---

## 🔧 Troubleshooting

### k6 cannot reach Laravel

When k6 runs inside Docker, `localhost` refers to the k6 container itself. Use the Docker service name/internal Compose network address instead.

### Video processing does not finish

Ensure the queue worker is running:

```bash
docker compose up -d queue
```

Then inspect it:

```bash
docker compose logs -f queue
```

### FFmpeg errors

Verify that the PHP/worker image contains the required FFmpeg tooling and inspect queue logs for the failing job.

### Cloudinary errors

Check the Cloudinary configuration in your local `.env`. Never commit those values.

### Grafana has no data

Verify Prometheus is running and k6 is executed with Prometheus remote-write output enabled:

```bash
docker compose logs -f prometheus
```

### Database/Redis problems

```bash
docker compose ps
docker compose logs -f db
docker compose logs -f redis
```

---

## 🔒 Security Notes

Before deploying outside local development:

- Use strong application secrets.
- Configure Cloudinary credentials securely.
- Never commit `.env` files containing secrets.
- Use production-appropriate database credentials.
- Review upload limits and validation rules.
- Review queue-worker resources and retry behavior.
- Protect Grafana and Prometheus from unintended public exposure.

---

## 🤝 Contributing

Contributions, ideas, and improvements are welcome.

```bash
git checkout -b feature/my-improvement
# make your changes
git commit -m "feat: describe the change"
git push origin feature/my-improvement
```

Then open a pull request with a clear description and testing notes.

---

## 📄 License

Wetube uses the **MIT License**, consistent with the Laravel application's project configuration.

---

## 👨‍💻 Author

**Muhammad-S-Gh**  
GitHub: [@Muhammad-S-Gh](https://github.com/Muhammad-S-Gh)

---

<p align="center">
  Built with Laravel, FFmpeg, Cloudinary, Docker, k6, Prometheus & Grafana.
</p>
