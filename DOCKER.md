# Docker Setup untuk RailSync Booking System

## Prasyarat
- Docker installed
- Docker Compose installed

## Struktur Services

Aplikasi ini menggunakan beberapa services dalam Docker:

1. **PHP-FPM** (port 9000)
   - Runtime untuk aplikasi Laravel
   - Image: php:8.2-fpm

2. **Nginx** (port 80)
   - Web server untuk serve aplikasi
   - Image: nginx:alpine

3. **MySQL** (port 3306)
   - Database relasional
   - Image: mysql:8.0
   - Database: `railsync_test`
   - User: `railsync` / Password: `secret`

4. **Redis** (port 6379)
   - Cache dan queue broker
   - Image: redis:7-alpine

5. **Testing Service**
   - Container terpisah untuk menjalankan unit/feature tests

## Quick Start

### 1. Build Docker Images
```bash
docker-compose build
```

### 2. Mulai Services
```bash
docker-compose up -d
```

### 3. Setup Database
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

### 4. Akses Aplikasi
- Web: http://localhost
- API: http://localhost/api

## Menjalankan Tests

### Jalankan Semua Tests
```bash
docker-compose run testing
```

### Jalankan Unit Tests Saja
```bash
docker-compose exec app php artisan test --filter=Unit
```

### Jalankan Feature Tests Saja
```bash
docker-compose exec app php artisan test --filter=Feature
```

### Jalankan Test Spesifik
```bash
docker-compose exec app php artisan test tests/Unit/BookingServiceTest.php
```

### Jalankan Tests dengan Coverage
```bash
docker-compose exec app php artisan test --coverage
```

## Commands Berguna

### Akses PHP Container
```bash
docker-compose exec app bash
```

### Akses MySQL
```bash
docker-compose exec mysql mysql -u railsync -psecret railsync_test
```

### Lihat Redis
```bash
docker-compose exec redis redis-cli
```

### Clear Cache & Config
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

### Restart Semua Services
```bash
docker-compose restart
```

### Stop Semua Services
```bash
docker-compose down
```

### Remove Volumes (clear data)
```bash
docker-compose down -v
```

## Services Testing

### BookingService Tests
Tests untuk create booking, validate data, seat reservation, dan payment expiry:
```bash
docker-compose exec app php artisan test tests/Unit/BookingServiceTest.php
```

**Test Cases:**
- ✓ Create booking successfully
- ✓ User not found
- ✓ Train not found
- ✓ Seat not found
- ✓ Seat already reserved
- ✓ Seat wrong train
- ✓ Confirm booking
- ✓ Expire pending payments
- ✓ Seat released on failure
- ✓ Seat price when available
- ✓ Train market price when seat price zero

### PaymentService Tests
Tests untuk payment processing dengan berbagai metode:
```bash
docker-compose exec app php artisan test tests/Unit/PaymentServiceTest.php
```

**Test Cases:**
- ✓ Payment with card method
- ✓ Payment with bank transfer
- ✓ Payment with cash method
- ✓ Payment fails unconfirmed booking
- ✓ Payment fails already paid
- ✓ Payment fails unsupported method
- ✓ Free booking payment
- ✓ Default payment method is card
- ✓ Payment reference generated
- ✓ Paid at timestamp set
- ✓ Payment expires at cleared

### Booking Flow Tests (Feature)
Integration tests untuk complete booking workflow:
```bash
docker-compose exec app php artisan test tests/Feature/BookingFlowTest.php
```

**Test Cases:**
- ✓ Complete booking workflow
- ✓ Booking cancellation
- ✓ Multiple bookings same user
- ✓ Booking status transitions

## Environment Variables

File `.env` untuk Docker:
```
APP_NAME=RailSync
APP_ENV=testing
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=railsync_test
DB_USERNAME=railsync
DB_PASSWORD=secret
REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
```

## Troubleshooting

### Container tidak start
```bash
docker-compose logs -f app
```

### Database connection error
```bash
docker-compose exec mysql mysql -h mysql -u railsync -psecret -e "SELECT 1"
```

### Redis connection error
```bash
docker-compose exec redis redis-cli ping
```

### Clear everything and start fresh
```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
docker-compose exec app php artisan migrate
```

## Architecture

```
┌──────────────────────────────────────────┐
│          Client Request (Port 80)        │
└──────────────────┬───────────────────────┘
                   │
        ┌──────────▼──────────┐
        │    Nginx (Alpine)   │
        │   Load Balancer     │
        └──────────┬──────────┘
                   │
        ┌──────────▼──────────┐
        │   PHP-FPM (8.2)     │
        │   Laravel App       │
        └──────────┬──────────┘
                   │
        ┌──────────┴─────────────────┐
        │                            │
   ┌────▼─────┐              ┌──────▼──────┐
   │  MySQL   │              │    Redis    │
   │ (Port    │              │  (Port      │
   │  3306)   │              │   6379)     │
   └──────────┘              └─────────────┘
```

## Running with Helper Script

Gunakan `docker.sh` untuk commands yang lebih mudah (untuk Linux/Mac):

```bash
chmod +x docker.sh

# Start
./docker.sh up

# Run tests
./docker.sh test

# Run specific tests
./docker.sh test BookingServiceTest

# Stop
./docker.sh down
```
