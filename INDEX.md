# 🚀 RailSync Microservices Architecture

Selamat datang! Berikut adalah sistem **5 Microservices yang terpisah** untuk RailSync Booking System.

---

## 📋 Struktur Microservices

```
├── 1️⃣ Auth Service (Port 8001) → Manajemen User & Autentikasi
├── 2️⃣ Train Service (Port 8002) → Manajemen Kereta & Kursi
├── 3️⃣ Booking Service (Port 8003) → Proses Pemesanan
├── 4️⃣ Payment Service (Port 8004) → Proses Pembayaran
├── 5️⃣ Notification Service (Port 8005) → Email & Queue Jobs
│
└── 🗄️ Database Layer (Separated)
    ├── Auth MySQL (3301)
    ├── Train MySQL (3302)
    ├── Booking MySQL (3303)
    ├── Payment MySQL (3304)
    └── Redis Shared (6379)
```

---

## 🎯 Fitur Utama

✅ **5 Microservices Terpisah** dengan database masing-masing  
✅ **Docker Infrastructure** - Ready untuk production  
✅ **REST API** untuk inter-service communication  
✅ **Queue System** dengan Redis dan output monitoring  
✅ **Postman Collection** untuk testing  
✅ **Setup Scripts** (Windows & Linux) - Automated setup  
✅ **Health Check Endpoints** untuk monitoring  
✅ **Comprehensive Documentation** untuk setiap service  

---

## ⚡ Quick Start

### Cara Tercepat (5 menit)

**Windows:**
```powershell
.\setup-microservices.bat
```

**Linux/WSL:**
```bash
chmod +x setup-microservices.sh
./setup-microservices.sh
```

**Lihat hasil**:
```
✅ Auth Service:        http://localhost:8001/api/health
✅ Train Service:       http://localhost:8002/api/health
✅ Booking Service:     http://localhost:8003/api/health
✅ Payment Service:     http://localhost:8004/api/health
✅ Notification Service: http://localhost:8005/api/health
```

---

## 📚 Dokumentasi

Pilih sesuai kebutuhan:

### 🔰 Pemula
➡️ **[QUICK_START.md](./QUICK_START.md)** - Mulai dalam 5 menit

### 📖 Detail Setup
➡️ **[MICROSERVICES_SETUP.md](./MICROSERVICES_SETUP.md)** - Panduan lengkap dengan semua command

### 💡 Technical Overview
➡️ **[IMPLEMENTATION_SUMMARY.md](./IMPLEMENTATION_SUMMARY.md)** - Apa yang sudah dibuat & struktur

### 🔍 Service Documentation
- **Auth Service**: [`services/auth-service/README.md`](./services/auth-service/README.md)
- **Train Service**: [`services/train-service/README.md`](./services/train-service/README.md)
- **Booking Service**: [`services/booking-service/README.md`](./services/booking-service/README.md)
- **Payment Service**: [`services/payment-service/README.md`](./services/payment-service/README.md)
- **Notification Service**: [`services/notification-service/README.md`](./services/notification-service/README.md)

---

## 🔌 API Testing

### Dengan Postman
1. **Open Postman**
2. **File → Import**
3. **Choose**: `postman_collection.json`
4. **Click** requests untuk test setiap service

### Dengan cURL

```bash
# Test Auth Service
curl http://localhost:8001/api/health

# Test Train Service
curl http://localhost:8002/api/health

# Test Booking Service
curl http://localhost:8003/api/health

# Test Payment Service
curl http://localhost:8004/api/health

# Test Notification Service
curl http://localhost:8005/api/health
```

---

## 📊 Monitoring

### Terminal 1 - View Logs (Real-time)
```bash
docker-compose -f docker-compose-microservices.yml logs -f
```

### Terminal 2 - Monitor Queue Jobs
```bash
docker-compose -f docker-compose-microservices.yml exec notification-app php artisan queue:work redis
```

Output akan terlihat:
```
[2024-01-15 10:30:45] Processing: SendBookingConfirmation
[2024-01-15 10:30:46] ✓ Email sent to user@example.com
[2024-01-15 10:30:47] Job completed successfully
```

### Terminal 3 - Check Service Status
```bash
docker-compose -f docker-compose-microservices.yml ps
```

---

## 🗄️ Database Access

Setiap service punya database terpisah:

```bash
# Auth Service
mysql -h 127.0.0.1 -P 3301 -u railsync -p"secret" railsync_auth

# Train Service
mysql -h 127.0.0.1 -P 3302 -u railsync -p"secret" railsync_train

# Booking Service
mysql -h 127.0.0.1 -P 3303 -u railsync -p"secret" railsync_booking

# Payment Service
mysql -h 127.0.0.1 -P 3304 -u railsync -p"secret" railsync_payment
```

---

## 🔄 Service Communication

Services berkomunikasi via HTTP REST API:

```
┌─────────────────────────────────────┐
│                                     │
│  Booking Service                    │
│  ├─→ Auth Service (verify user)     │
│  ├─→ Train Service (check seat)     │
│  └─→ Queue (send notification)      │
│                                     │
└─────────────────────────────────────┘
```

Environment variables sudah dikonfigurasi:
- `AUTH_SERVICE_URL=http://auth-app:8000`
- `TRAIN_SERVICE_URL=http://train-app:8000`
- `BOOKING_SERVICE_URL=http://booking-app:8000`
- `PAYMENT_SERVICE_URL=http://payment-app:8000`

---

## 🛠️ Common Tasks

### Start Services
```bash
docker-compose -f docker-compose-microservices.yml up -d
```

### Stop Services
```bash
docker-compose -f docker-compose-microservices.yml down
```

### Reset Database (Fresh Start)
```bash
docker-compose -f docker-compose-microservices.yml down -v
docker-compose -f docker-compose-microservices.yml up -d
docker-compose -f docker-compose-microservices.yml exec auth-app php artisan migrate
docker-compose -f docker-compose-microservices.yml exec train-app php artisan migrate
docker-compose -f docker-compose-microservices.yml exec booking-app php artisan migrate
docker-compose -f docker-compose-microservices.yml exec payment-app php artisan migrate
```

### Run Artisan Command
```bash
# On Auth Service
docker-compose -f docker-compose-microservices.yml exec auth-app php artisan list

# On any service
docker-compose -f docker-compose-microservices.yml exec <service>-app php artisan <command>
```

---

## 📁 Struktur Folder

```
Railsyncbookingsystem/
│
├── 📄 docker-compose-microservices.yml    ← Main Docker config
├── 🎯 setup-microservices.bat             ← Windows setup
├── 🎯 setup-microservices.sh              ← Linux/WSL setup
├── 📮 postman_collection.json             ← Postman API collection
│
├── 📚 Documentation
│   ├── QUICK_START.md                     ← Start here!
│   ├── MICROSERVICES_SETUP.md             ← Full guide
│   ├── IMPLEMENTATION_SUMMARY.md          ← Technical details
│   └── INDEX.md                           ← This file
│
└── 📦 services/
    ├── auth-service/                      ← Auth microservice
    ├── train-service/                     ← Train microservice
    ├── booking-service/                   ← Booking microservice
    ├── payment-service/                   ← Payment microservice
    └── notification-service/              ← Notification service
```

---

## ✅ Checklist Setup

- [ ] Run setup script (`.bat` atau `.sh`)
- [ ] Tunggu semua services ready (5-10 menit)
- [ ] Test health endpoints (curl atau Postman)
- [ ] Import Postman collection
- [ ] Monitor queue worker
- [ ] Test database connectivity
- [ ] Ready untuk development!

---

## 🚨 Troubleshooting

### Services won't start?
```bash
# Check status
docker-compose -f docker-compose-microservices.yml ps

# View logs
docker-compose -f docker-compose-microservices.yml logs auth-app
```

### Port already in use?
```bash
# Windows
netstat -ano | findstr :8001
taskkill /PID <PID> /F

# Linux
lsof -i :8001
kill -9 <PID>
```

### Database error?
```bash
docker-compose -f docker-compose-microservices.yml exec auth-app php artisan tinker
>>> DB::connection()->getPdo()
```

---

## 🔗 Useful Links

- **Docker Desktop**: https://www.docker.com/products/docker-desktop
- **Postman**: https://www.postman.com/downloads/
- **Laravel Docs**: https://laravel.com/docs
- **MySQL Docs**: https://dev.mysql.com/doc/
- **Redis Docs**: https://redis.io/documentation

---

## 📝 Next Steps

1. ✅ **Run setup script** → Jalankan `setup-microservices.bat` atau `setup-microservices.sh`
2. ✅ **Test health endpoints** → Buka Postman atau cURL untuk verify
3. ✅ **Import Postman collection** → `postman_collection.json`
4. ✅ **Monitor queue** → Lihat output dari Notification Service
5. ✅ **Development** → Mulai tambahkan business logic ke setiap service

---

## 💡 Tips

- 📖 Baca **QUICK_START.md** untuk panduan 5 menit
- 🔍 Check **MICROSERVICES_SETUP.md** untuk detail lengkap
- 📊 Monitor logs dengan `docker-compose logs -f`
- 🧪 Test dengan Postman collection yang sudah disediakan
- 💬 Setiap service punya README sendiri dengan info spesifik

---

## ✨ Ready to Rock!

Semuanya sudah siap. Berikutnya tinggal:

1. Run setup script
2. Test endpoints
3. Mulai development!

**Happy Coding!** 🚀

---

**Last Updated**: January 2024  
**Version**: 1.0 - Complete Microservices Architecture
