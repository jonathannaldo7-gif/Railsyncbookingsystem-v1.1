# Booking Service

Microservice untuk proses pemesanan dalam RailSync.

## 📌 Spesifikasi

- **Port API**: 8003
- **Port Nginx**: 9003
- **Database**: MySQL (railsync_booking pada port 3303)
- **Queue**: Redis
- **GraphQL**: Ya (via Lighthouse)

## 🔌 Endpoints

### REST API
- `GET /api/health` - Health check
- `GET /api/status` - Service status

### GraphQL
- `POST /graphql` - GraphQL queries/mutations

## 🗄️ Database

Koneksi:
```
Host: booking-mysql
Port: 3306
Database: railsync_booking
Username: railsync
Password: secret
```

### Tables
- `migrations_log` - Tracking migrations

## 🚀 Startup

```bash
# Build image
docker-compose -f docker-compose-microservices.yml build booking-app

# Start service
docker-compose -f docker-compose-microservices.yml up -d booking-app booking-mysql

# Run migrations
docker-compose -f docker-compose-microservices.yml exec booking-app php artisan migrate

# Test health
curl http://localhost:8003/api/health
```

## 📊 Queue

Booking Service menggunakan Redis queue untuk async tasks dan notifications.

```bash
# Monitor queue
docker-compose -f docker-compose-microservices.yml exec booking-app php artisan queue:work redis
```

## 🔗 Dependencies

Booking Service dapat memanggil:
- **Auth Service**: http://auth-app:8000/api
- **Train Service**: http://train-app:8000/api

## 📝 Inter-Service Calls

Service lain dapat menghubungi Booking Service di:
```
http://booking-app:8000/api
```

Environment variables:
```
BOOKING_SERVICE_URL=http://booking-app:8000
AUTH_SERVICE_URL=http://auth-app:8000
TRAIN_SERVICE_URL=http://train-app:8000
```

## 🔍 Debugging

Lihat logs:
```bash
docker-compose -f docker-compose-microservices.yml logs -f booking-app
```

Akses database:
```bash
mysql -h 127.0.0.1 -P 3303 -u railsync -p"secret" railsync_booking
```
