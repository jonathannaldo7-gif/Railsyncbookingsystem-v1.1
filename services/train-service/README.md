# Train Service

Microservice untuk manajemen data kereta dan kursi dalam RailSync.

## 📌 Spesifikasi

- **Port API**: 8002
- **Port Nginx**: 9002
- **Database**: MySQL (railsync_train pada port 3302)
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
Host: train-mysql
Port: 3306
Database: railsync_train
Username: railsync
Password: secret
```

### Tables
- `migrations_log` - Tracking migrations

## 🚀 Startup

```bash
# Build image
docker-compose -f docker-compose-microservices.yml build train-app

# Start service
docker-compose -f docker-compose-microservices.yml up -d train-app train-mysql

# Run migrations
docker-compose -f docker-compose-microservices.yml exec train-app php artisan migrate

# Test health
curl http://localhost:8002/api/health
```

## 📊 Queue

Train Service menggunakan Redis queue untuk async tasks.

```bash
# Monitor queue
docker-compose -f docker-compose-microservices.yml exec train-app php artisan queue:work redis
```

## 🔗 Dependencies

Train Service dapat memanggil:
- **Auth Service**: http://auth-app:8000/api

## 📝 Inter-Service Calls

Service lain dapat menghubungi Train Service di:
```
http://train-app:8000/api
```

Environment variable:
```
TRAIN_SERVICE_URL=http://train-app:8000
```

## 🔍 Debugging

Lihat logs:
```bash
docker-compose -f docker-compose-microservices.yml logs -f train-app
```

Akses database:
```bash
mysql -h 127.0.0.1 -P 3302 -u railsync -p"secret" railsync_train
```
