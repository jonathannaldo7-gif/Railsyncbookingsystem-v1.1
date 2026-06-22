# Notification Service

Microservice untuk mengirim notifikasi (email, sms, dll) dalam RailSync.

## 📌 Spesifikasi

- **Port API**: 8005
- **Port Nginx**: 9005
- **Database**: None (Stateless)
- **Queue**: Redis
- **GraphQL**: Tidak (untuk processing jobs saja)

## 🔌 Endpoints

### REST API
- `GET /api/health` - Health check (Redis only)
- `GET /api/status` - Service status

## 🚀 Startup

```bash
# Build image
docker-compose -f docker-compose-microservices.yml build notification-app

# Start service
docker-compose -f docker-compose-microservices.yml up -d notification-app

# Test health
curl http://localhost:8005/api/health
```

## 📊 Queue Worker

Notification Service adalah **queue worker** yang memproses jobs dari Redis.

```bash
# Start queue worker (foreground - see output clearly)
docker-compose -f docker-compose-microservices.yml exec notification-app php artisan queue:work redis

# Start queue worker (background)
docker-compose -f docker-compose-microservices.yml exec -d notification-app php artisan queue:work redis

# Monitor failed jobs
docker-compose -f docker-compose-microservices.yml exec notification-app php artisan queue:failed

# Retry failed jobs
docker-compose -f docker-compose-microservices.yml exec notification-app php artisan queue:retry all
```

## 🔗 Queue Jobs

Notification Service memproses jobs yang dipush oleh:
- **Booking Service**: Booking confirmation emails
- **Payment Service**: Payment receipt emails
- **Auth Service**: Account notifications

## 📝 Job Output

Setiap job diproses dengan output yang jelas:

```
[2024-01-15 10:30:45] Processing: SendBookingConfirmation
[2024-01-15 10:30:46] ✓ Email sent to user@example.com
[2024-01-15 10:30:47] Job completed successfully
```

## 📧 Mail Configuration

Di `.env`:
```
MAIL_DRIVER=log
MAIL_FROM_ADDRESS=notifications@railsync.com
```

Untuk production, ubah ke:
- `MAIL_DRIVER=smtp` atau `mailgun` atau `sendgrid`

## 🔗 Dependencies

Notification Service dapat memanggil:
- **Auth Service**: http://auth-app:8000/api (untuk user data)
- **Booking Service**: http://booking-app:8000/api (untuk booking data)

## 🔍 Debugging

Lihat logs real-time:
```bash
docker-compose -f docker-compose-microservices.yml logs -f notification-app
```

Monitoring Redis queue:
```bash
redis-cli -p 6379
> LLEN queues:default
> LRANGE queues:default 0 -1
```

## 📝 Environment

```bash
REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
MAIL_DRIVER=log
```
