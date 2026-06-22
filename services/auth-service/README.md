# Auth Service

Microservice untuk manajemen autentikasi dan user dalam RailSync.

## 📌 Spesifikasi

- **Port API**: 8001
- **Port Nginx**: 9001
- **Database**: MySQL (railsync_auth pada port 3301)
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
Host: auth-mysql
Port: 3306
Database: railsync_auth
Username: railsync
Password: secret
```

### Tables
- `migrations_log` - Tracking migrations

## 🚀 Startup

```bash
# Build image
docker-compose -f docker-compose-microservices.yml build auth-app

# Start service
docker-compose -f docker-compose-microservices.yml up -d auth-app auth-mysql

# Run migrations
docker-compose -f docker-compose-microservices.yml exec auth-app php artisan migrate

# Test health
curl http://localhost:8001/api/health
```

## 📊 Queue

Auth Service menggunakan Redis queue untuk async tasks.

```bash
# Monitor queue
docker-compose -f docker-compose-microservices.yml exec auth-app php artisan queue:work redis
```

## 📝 Inter-Service Calls

Service lain dapat menghubungi Auth Service di:
```
http://auth-app:8000/api
```

Environment variable:
```
AUTH_SERVICE_URL=http://auth-app:8000
```

## 🔍 Debugging

Lihat logs:
```bash
docker-compose -f docker-compose-microservices.yml logs -f auth-app
```

Akses database:
```bash
mysql -h 127.0.0.1 -P 3301 -u railsync -p"secret" railsync_auth
```

Akses Redis:
```bash
redis-cli -p 6379
```
