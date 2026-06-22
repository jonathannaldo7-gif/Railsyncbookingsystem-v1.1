# RailSync Microservices - Quick Start Guide

Panduan cepat untuk menjalankan 5 microservices RailSync dalam 5 menit.

---

## ⏱️ Quick Setup (5 minutes)

### Step 1: Run Setup Script

**Windows (PowerShell as Admin):**
```powershell
cd c:\xampp\htdocs\Railsyncbookingsystem
.\setup-microservices.bat
```

**Linux/WSL:**
```bash
cd /path/to/Railsyncbookingsystem
chmod +x setup-microservices.sh
./setup-microservices.sh
```

Script akan otomatis:
- ✓ Copy `.env` files
- ✓ Generate APP_KEY
- ✓ Build Docker images
- ✓ Start all 5 services
- ✓ Run migrations
- ✓ Test health endpoints

### Step 2: Verify Services Running

Buka browser dan test:

```
Auth Service:        http://localhost:8001/api/health
Train Service:       http://localhost:8002/api/health
Booking Service:     http://localhost:8003/api/health
Payment Service:     http://localhost:8004/api/health
Notification Service: http://localhost:8005/api/health
```

Jika melihat JSON response dengan `"status": "healthy"` → **Semua OK!** ✅

---

## 📱 Testing dengan Postman

1. **Open Postman**
2. **Click "Import"**
3. **Choose file**: `postman_collection.json`
4. **Click "Health Check"** di setiap service

Expected response:
```json
{
  "service": "auth-service",
  "status": "healthy",
  "timestamp": "2024-01-15T10:30:45.000000Z",
  "database": "connected",
  "redis": "connected"
}
```

---

## 👀 Monitor Services

Buka **3 terminal berbeda**:

### Terminal 1 - View Logs
```bash
docker-compose -f docker-compose-microservices.yml logs -f
```

### Terminal 2 - Monitor Queue Jobs
```bash
docker-compose -f docker-compose-microservices.yml exec notification-app php artisan queue:work redis
```

Output akan menampilkan:
```
[2024-01-15 10:30:45] Processing: SendBookingConfirmation
[2024-01-15 10:30:46] ✓ Email sent to user@example.com
```

### Terminal 3 - Run Tests (Optional)
```bash
# Test specific service
curl http://localhost:8001/api/health | jq

# Or test all services
for port in 8001 8002 8003 8004 8005; do
  echo "Testing port $port..."
  curl http://localhost:$port/api/health
  echo ""
done
```

---

## 🗄️ Database Access

Setiap service punya database sendiri:

```bash
# Auth Service Database
mysql -h 127.0.0.1 -P 3301 -u railsync -p"secret" railsync_auth

# Train Service Database
mysql -h 127.0.0.1 -P 3302 -u railsync -p"secret" railsync_train

# Booking Service Database
mysql -h 127.0.0.1 -P 3303 -u railsync -p"secret" railsync_booking

# Payment Service Database
mysql -h 127.0.0.1 -P 3304 -u railsync -p"secret" railsync_payment
```

Dari MySQL CLI:
```sql
SHOW TABLES;
SELECT * FROM migrations_log;
```

---

## 🔧 Common Commands

### Start Services
```bash
docker-compose -f docker-compose-microservices.yml up -d
```

### Stop Services
```bash
docker-compose -f docker-compose-microservices.yml down
```

### View Status
```bash
docker-compose -f docker-compose-microservices.yml ps
```

### View Service Logs
```bash
# All services
docker-compose -f docker-compose-microservices.yml logs -f

# Specific service
docker-compose -f docker-compose-microservices.yml logs -f auth-app

# Real-time with 100 last lines
docker-compose -f docker-compose-microservices.yml logs -f --tail=100
```

### Run Artisan Commands
```bash
# On Auth Service
docker-compose -f docker-compose-microservices.yml exec auth-app php artisan list

# On Booking Service
docker-compose -f docker-compose-microservices.yml exec booking-app php artisan migrate

# On Notification Service (Queue Worker)
docker-compose -f docker-compose-microservices.yml exec notification-app php artisan queue:work redis
```

### Fresh Database (Reset)
```bash
docker-compose -f docker-compose-microservices.yml down -v
docker-compose -f docker-compose-microservices.yml up -d
docker-compose -f docker-compose-microservices.yml exec auth-app php artisan migrate
docker-compose -f docker-compose-microservices.yml exec train-app php artisan migrate
docker-compose -f docker-compose-microservices.yml exec booking-app php artisan migrate
docker-compose -f docker-compose-microservices.yml exec payment-app php artisan migrate
```

---

## 📊 Service Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    RailSync Microservices                   │
└─────────────────────────────────────────────────────────────┘

┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│  Auth API    │    │  Train API   │    │ Booking API  │
│  :8001       │    │  :8002       │    │  :8003       │
└──────┬───────┘    └──────┬───────┘    └──────┬───────┘
       │                   │                   │
       └───────────────────┴───────────────────┘
                    │
           ┌────────┴────────┐
           │                 │
     ┌──────────────┐  ┌──────────────┐
     │ Payment API  │  │  Notification│
     │  :8004       │  │  :8005 Queue │
     └──────────────┘  └──────────────┘

       Database Layer (Separated)
     ┌──────────────────────────────────┐
     │  Auth DB  │ Train DB │ Booking DB │ Payment DB
     │  :3301    │  :3302   │  :3303     │  :3304
     └──────────────────────────────────┘

     Shared Resources
     ┌──────────────────────────────────┐
     │          Redis Queue             │
     │  (Port 6379)                     │
     │  - Cache                         │
     │  - Sessions                      │
     │  - Jobs                          │
     └──────────────────────────────────┘
```

---

## 🎯 API Ports

| Service | API Port | Nginx | DB Port |
|---------|----------|-------|---------|
| Auth | 8001 | 9001 | 3301 |
| Train | 8002 | 9002 | 3302 |
| Booking | 8003 | 9003 | 3303 |
| Payment | 8004 | 9004 | 3304 |
| Notification | 8005 | 9005 | - |
| Redis | - | - | 6379 |

---

## ❌ Troubleshooting

### "Port already in use"
```bash
# Windows - Find and kill process
netstat -ano | findstr :8001
taskkill /PID <PID> /F

# Linux - Find and kill process
lsof -i :8001
kill -9 <PID>
```

### "Connection refused"
```bash
# Ensure Docker Desktop is running
# Check status
docker-compose -f docker-compose-microservices.yml ps

# If not running, start
docker-compose -f docker-compose-microservices.yml up -d
```

### "Database error"
```bash
# Check database container logs
docker-compose -f docker-compose-microservices.yml logs auth-mysql

# Verify connectivity
docker-compose -f docker-compose-microservices.yml exec auth-app php artisan tinker
>>> DB::connection()->getPdo()
```

### "Queue not processing"
```bash
# Start queue worker in foreground (see output)
docker-compose -f docker-compose-microservices.yml exec notification-app php artisan queue:work redis

# Check Redis connection
docker-compose -f docker-compose-microservices.yml exec notification-app php artisan tinker
>>> Redis::ping()
```

---

## 📚 Documentation

- **Setup Guide**: [`MICROSERVICES_SETUP.md`](./MICROSERVICES_SETUP.md)
- **Implementation Details**: [`IMPLEMENTATION_SUMMARY.md`](./IMPLEMENTATION_SUMMARY.md)
- **Auth Service**: [`services/auth-service/README.md`](./services/auth-service/README.md)
- **Train Service**: [`services/train-service/README.md`](./services/train-service/README.md)
- **Booking Service**: [`services/booking-service/README.md`](./services/booking-service/README.md)
- **Payment Service**: [`services/payment-service/README.md`](./services/payment-service/README.md)
- **Notification Service**: [`services/notification-service/README.md`](./services/notification-service/README.md)

---

## ✅ Checklist

- [ ] Run `setup-microservices.bat` atau `setup-microservices.sh`
- [ ] Semua 5 services berjalan (`docker ps`)
- [ ] Test health endpoints (curl atau Postman)
- [ ] Import Postman collection
- [ ] Monitor queue worker di terminal
- [ ] Test database connectivity
- [ ] Check Redis connection

---

**Semuanya siap untuk development!** 🚀

Untuk pertanyaan lebih lanjut, lihat `MICROSERVICES_SETUP.md` atau README di masing-masing service.
