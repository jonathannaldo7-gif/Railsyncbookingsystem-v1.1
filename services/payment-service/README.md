# Payment Service

Microservice untuk proses pembayaran dalam RailSync.

## 📌 Spesifikasi

- **Port API**: 8004
- **Port Nginx**: 9004
- **Database**: MySQL (railsync_payment pada port 3304)
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
Host: payment-mysql
Port: 3306
Database: railsync_payment
Username: railsync
Password: secret
```

### Tables
- `migrations_log` - Tracking migrations

## 🚀 Startup

```bash
# Build image
docker-compose -f docker-compose-microservices.yml build payment-app

# Start service
docker-compose -f docker-compose-microservices.yml up -d payment-app payment-mysql

# Run migrations
docker-compose -f docker-compose-microservices.yml exec payment-app php artisan migrate

# Test health
curl http://localhost:8004/api/health
```

## 📊 Queue

Payment Service menggunakan Redis queue untuk async payment processing.

```bash
# Monitor queue
docker-compose -f docker-compose-microservices.yml exec payment-app php artisan queue:work redis
```

## 🔗 Dependencies

Payment Service dapat memanggil:
- **Auth Service**: http://auth-app:8000/api
- **Booking Service**: http://booking-app:8000/api

## 📝 Inter-Service Calls

Service lain dapat menghubungi Payment Service di:
```
http://payment-app:8000/api
```

Environment variables:
```
PAYMENT_SERVICE_URL=http://payment-app:8000
AUTH_SERVICE_URL=http://auth-app:8000
BOOKING_SERVICE_URL=http://booking-app:8000
```

## 🔍 Debugging

Lihat logs:
```bash
docker-compose -f docker-compose-microservices.yml logs -f payment-app
```

Akses database:
```bash
mysql -h 127.0.0.1 -P 3304 -u railsync -p"secret" railsync_payment
```
