# Testing Guide - RailSync Booking System

## Overview

Testing setup menggunakan Docker containers dengan MySQL dan Redis. Semua services dan dependencies sudah dikonfigurasi di `docker-compose.yml`.

## Test Structure

```
tests/
├── Unit/
│   ├── BookingServiceTest.php      # 11 test cases
│   └── PaymentServiceTest.php      # 10 test cases
└── Feature/
    └── BookingFlowTest.php         # 4 integration test cases
```

**Total: 25 Test Cases**

## Services Tested

### 1. BookingService
File: [app/Services/BookingService.php](app/Services/BookingService.php)

**Functionalities:**
- Create booking dengan validasi user, train, seat
- Reserve seat (mark as unavailable)
- Confirm booking dengan payment expiry
- Expire pending payments
- Release seat jika booking fails

**Test Cases:**
1. Create booking successfully
2. User not found (error handling)
3. Train not found (error handling)
4. Seat not found (error handling)
5. Seat already reserved (error handling)
6. Seat wrong train (validation)
7. Confirm booking (status update)
8. Expire pending payments (cleanup)
9. Seat released on failure (rollback)
10. Booking uses seat price (pricing priority)
11. Booking uses train market price (fallback)

### 2. PaymentService
File: [app/Services/PaymentService.php](app/Services/PaymentService.php)

**Functionalities:**
- Process booking payment dengan berbagai metode
- Validate payment requirements
- Generate payment reference
- Handle free bookings
- Update payment status dan timestamps

**Payment Methods Supported:**
- `card` - Credit/Debit card
- `bank_transfer` - Bank transfer
- `cash` - Cash payment

**Test Cases:**
1. Process payment with card method
2. Process payment with bank transfer method
3. Process payment with cash method
4. Payment fails unconfirmed booking
5. Payment fails already paid
6. Payment fails unsupported method
7. Free booking payment (amount = 0)
8. Default payment method is card
9. Payment reference generated correctly
10. Paid at timestamp is set
11. Payment expires at is cleared

### 3. Booking Flow (Integration)
File: [tests/Feature/BookingFlowTest.php](tests/Feature/BookingFlowTest.php)

**Test Cases:**
1. Complete booking workflow (create → confirm → pay)
2. Booking cancellation dengan seat release
3. Multiple bookings same user different seats
4. Booking status transitions

## Prerequisites

### Minimum Requirements
- Docker 20.10+
- Docker Compose 2.0+
- 4GB RAM
- 2GB free disk space

### Optional
- PHP 8.2+ (untuk running tests locally)
- MySQL 8.0+ (untuk local development)
- Redis 7+ (untuk queue/cache)

## Quick Start

### 1. Build and Start Containers
```bash
docker-compose build
docker-compose up -d
```

### 2. Run Database Migrations
```bash
docker-compose exec app php artisan migrate
```

### 3. Run All Tests
```bash
docker-compose run testing
```

## Running Tests

### Run All Tests
```bash
docker-compose run testing
```

### Run Specific Test Suite

**Unit Tests Only:**
```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml --testsuite=Unit
```

**Feature Tests Only:**
```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml --testsuite=Feature
```

### Run Specific Test File

**BookingService Tests:**
```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml tests/Unit/BookingServiceTest.php
```

**PaymentService Tests:**
```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml tests/Unit/PaymentServiceTest.php
```

**Booking Flow Tests:**
```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml tests/Feature/BookingFlowTest.php
```

### Run Specific Test Method

```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml \
  --filter test_create_booking_successfully \
  tests/Unit/BookingServiceTest.php
```

### Run With Coverage Report

```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml \
  --coverage-html=coverage
```

Coverage report akan tersimpan di `coverage/` directory.

### Run With Verbose Output

```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml -v
```

### Run With Watch Mode (auto-rerun on changes)

```bash
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml \
  --watch \
  tests/Unit/BookingServiceTest.php
```

## Using Helper Scripts

### Linux/Mac
```bash
chmod +x docker.sh

# Start services
./docker.sh up

# Run all tests
./docker.sh test

# Run specific tests
./docker.sh test BookingServiceTest
./docker.sh test PaymentServiceTest

# Run specific test method
./docker.sh test "test_create_booking_successfully"

# View logs
./docker.sh logs

# Stop services
./docker.sh down
```

### Windows
```bash
# Start services
docker.bat up

# Run all tests
docker.bat test

# Run specific tests
docker.bat test BookingServiceTest
docker.bat test PaymentServiceTest

# Stop services
docker.bat down
```

## Database Setup for Testing

Database otomatis:
1. Created saat services start
2. Migrated saat testing service runs
3. Seeded dengan factory data (jika diperlukan)
4. Cleared/refreshed antara test runs (RefreshDatabase trait)

### Manual Database Setup

```bash
# Access database
docker-compose exec mysql mysql -u railsync -psecret railsync_test

# Run migrations manually
docker-compose exec app php artisan migrate

# Seed with test data
docker-compose exec app php artisan db:seed

# Reset database
docker-compose exec app php artisan migrate:fresh
```

## Test Data (Factories)

Factories sudah ada di `database/factories/`:
- `UserFactory.php` - Generate test users
- Model factories dapat di-create dengan: `php artisan make:factory ModelNameFactory`

Digunakan dalam tests:
```php
$user = User::factory()->create();
$train = Train::factory()->create(['market_price' => 150000]);
$seat = Seat::factory()->create(['train_id' => $train->id]);
$booking = Booking::factory()->create(['status' => 'confirmed']);
```

## Environment Configuration

### Docker Testing Environment (phpunit.docker.xml)
```
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=railsync_test
REDIS_HOST=redis
QUEUE_CONNECTION=redis
CACHE_DRIVER=array
SESSION_DRIVER=array
```

### Local Development Environment (phpunit.xml)
```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
QUEUE_CONNECTION=sync
CACHE_DRIVER=array
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests
on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: railsync_test
          MYSQL_USER: railsync
          MYSQL_PASSWORD: secret
          MYSQL_ROOT_PASSWORD: root_secret
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
      redis:
        image: redis:7-alpine
        options: >-
          --health-cmd="redis-cli ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v3
      - uses: php-actions/setup-php@v1
        with:
          php-version: '8.2'
      - run: composer install
      - run: vendor/bin/phpunit
```

## Troubleshooting

### MySQL Connection Error
```bash
# Check MySQL service
docker-compose logs mysql

# Verify connection
docker-compose exec app mysql -h mysql -u railsync -psecret -e "SELECT 1"

# Restart MySQL
docker-compose restart mysql
```

### Redis Connection Error
```bash
# Check Redis service
docker-compose logs redis

# Verify connection
docker-compose exec redis redis-cli ping

# Restart Redis
docker-compose restart redis
```

### Tests Failing Due to Database
```bash
# Clear and rebuild database
docker-compose exec app php artisan migrate:fresh

# Run tests again
docker-compose run testing
```

### Memory Issues
```bash
# Increase Docker memory limit in Docker Desktop
# Settings > Resources > Memory (set to 4GB or more)

# Or reduce test parallelization
docker-compose exec app vendor/bin/phpunit -c phpunit.docker.xml \
  --processes=1
```

### PHPUnit Not Found
```bash
# Install dev dependencies
docker-compose exec app composer install --dev

# Verify installation
docker-compose exec app vendor/bin/phpunit --version
```

## Performance Tips

1. **Run specific tests** instead of all tests during development
2. **Use --processes** for parallel test execution
3. **Use --testsuite** to run only Unit or Feature tests
4. **Clear cache** between test runs if needed
5. **Use RefreshDatabase** for database cleanup (already implemented)

## Best Practices

1. ✅ Each test should be independent
2. ✅ Use factories for test data
3. ✅ Test both success and failure scenarios
4. ✅ Use meaningful test names
5. ✅ Keep tests DRY (Don't Repeat Yourself)
6. ✅ Mock external dependencies
7. ✅ Arrange-Act-Assert pattern

## Adding New Tests

1. Create test file in `tests/Unit/` or `tests/Feature/`
2. Extend `Tests\TestCase`
3. Use `RefreshDatabase` trait for database cleanup
4. Follow naming convention: `{Action}Test.php`

Example:
```php
<?php

namespace Tests\Unit;

use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyNewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_something(): void
    {
        // Arrange
        $data = [];

        // Act
        $result = new MyService()->doSomething($data);

        // Assert
        $this->assertTrue($result);
    }
}
```

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Testing Guide](https://laravel.com/docs/testing)
- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
