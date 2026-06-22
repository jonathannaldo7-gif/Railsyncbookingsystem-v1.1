@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Railsync Booking System</h1>
        <div id="dashboard-message"></div>
    </div>

    <div class="card" id="login-card">
        <h2>Login / Register</h2>
        <div id="message-container"></div>
        <div class="grid grid-2">
            <div>
                <h3>Login</h3>
                <label for="login-email">Email</label>
                <input id="login-email" type="email" placeholder="user@example.com">
                <label for="login-password">Password</label>
                <input id="login-password" type="password" placeholder="Password">
                <button id="login-user">Login</button>
            </div>
            <div>
                <h3>Register</h3>
                <label for="register-name">Name</label>
                <input id="register-name" type="text" placeholder="Full name">
                <label for="register-email">Email</label>
                <input id="register-email" type="email" placeholder="user@example.com">
                <label for="register-password">Password</label>
                <input id="register-password" type="password" placeholder="Minimum 8 characters">
                <button id="register-user">Register</button>
                <p class="note">Buat akun baru untuk masuk dan melakukan reservasi.</p>
            </div>
        </div>
        <p class="note".</p>
    </div>

    <div class="card" id="booking-card" style="display: none;">
        <h2>Reservasi Saya</h2>
        <div class="table-wrapper">
            <table id="bookings-table">
                <thead>
                    <tr><th>ID</th><th>Kereta</th><th>Kursi</th><th>Tanggal</th><th>Status</th><th>Jumlah Bayar</th><th>Pembayaran</th><th>Sisa Waktu</th><th>Aksi</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card" id="admin-booking-card" style="display: none;">
        <h2>ACC Reservasi (Admin)</h2>
        <div class="table-wrapper">
            <table id="admin-bookings-table">
                <thead>
                    <tr><th>No</th><th>Penumpang</th><th>Kereta</th><th>Kursi</th><th>Tanggal</th><th>Status</th><th>Jumlah Bayar</th><th>Pembayaran</th><th>Aksi</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card" id="train-card" style="display: none;">
        <h2>Daftar Kereta</h2>
        <table id="trains-table">
            <thead>
                <tr><th>No</th><th>Nama KA</th><th>Rute</th><th>Aksi</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card" id="admin-train-bookings-card" style="display: none;">
        <h2>Reservasi Kereta: <span id="admin-selected-train-name"></span></h2>
        <table id="admin-train-bookings-table">
                <thead>
                <tr><th>No</th><th>Penumpang</th><th>Kursi</th><th>Tanggal</th><th>Status</th><th>Jumlah Bayar</th><th>Pembayaran</th><th>Aksi</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card" id="admin-paid-card" style="display: none;">
        <h2>Penumpang Terbayar per Kereta (Monitoring)</h2>
        <div class="table-wrapper">
            <table id="admin-paid-table">
                <thead>
                    <tr><th>Kereta</th><th>Penumpang</th><th>Kursi</th><th>Tanggal</th><th>Jumlah Bayar</th><th>Waktu Bayar</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card" id="seat-card" style="display: none;">
        <h2>Informasi Tiket Kereta</h2>
        <div class="ticket-card">
            <div class="ticket-summary">
                <h3>Info Kereta</h3>
                <div id="selected-train-schedule" class="ticket-detail">Pilih kereta terlebih dahulu untuk melihat jadwal.</div>
            </div>
            <div class="seat-selection">
                <h3>Pilih Kursi</h3>
                <div class="seat-card-info">
                    <div id="seat-info"><strong>2. Pilih kursi untuk melihat detail dan pesan.</strong></div>
                </div>
                <div class="seat-grid" id="seat-grid"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let selectedTrainId = null;
    let selectedTrain = null;
    let selectedSeat = null;

    function showMessage(message, type = 'success', containerId = 'message-container') {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = `<div class="alert ${type}">${message}</div>`;
        setTimeout(() => { container.innerHTML = ''; }, 5000);
    }

    function formatDateTime(value) {
        if (!value) return '-';
        const date = new Date(value);
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const dateString = date.toLocaleDateString('id-ID');
        const timeString = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        return `${days[date.getDay()]}, ${dateString} ${timeString}`;
    }

    async function loadTrains() {
        const { body } = await requestJson('GET', '/api/trains');
        const tbody = document.querySelector('#trains-table tbody');
        tbody.innerHTML = '';
        if (!body.data || body.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4">Belum ada kereta.</td></tr>';
            return;
        }
        body.data.forEach((train, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${train.name}</td>
                <td>${train.route}</td>
                <td><button class="secondary select-train-button">Pilih</button></td>
            `;
            row.querySelector('.select-train-button').addEventListener('click', () => selectTrain(train));
            tbody.appendChild(row);
        });
    }

    async function loadAdminBookings() {
        const { body } = await requestJson('GET', '/api/bookings');
        const tbody = document.querySelector('#admin-bookings-table tbody');
        tbody.innerHTML = '';

        const pendingBookings = body.data.filter(booking => booking.status === 'pending');

        if (pendingBookings.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9">Tidak ada reservasi pending.</td></tr>';
            return;
        }

        pendingBookings.forEach((booking, idx) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${idx + 1}</td>
                <td>${booking.user ? booking.user.name : booking.user_id}</td>
                <td>${booking.train ? booking.train.name : booking.train_id}</td>
                <td>${booking.seat ? booking.seat.seat_number : booking.seat_id}</td>
                <td>${new Date(booking.booking_date).toLocaleDateString()}</td>
                <td>${formatStatus(booking.status)}</td>
                <td>${Number(booking.payment_amount).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</td>
                <td>${formatPaymentStatus(booking.payment_status)}</td>
                <td>${booking.status === 'pending' ? `<button class="secondary" onclick="confirmBooking(${booking.id})">ACC</button>` : ''}</td>
            `;
            tbody.appendChild(row);
        });
    }

    async function loadAdminPaidUsers() {
        const { body } = await requestJson('GET', '/api/bookings');
        const tbody = document.querySelector('#admin-paid-table tbody');
        tbody.innerHTML = '';

        const paid = (body.data || []).filter(b => b.payment_status === 'paid');
        if (paid.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">Belum ada pembayaran terkonfirmasi.</td></tr>';
            return;
        }

        paid.forEach(booking => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${booking.train ? booking.train.name : booking.train_id}</td>
                <td>${booking.user ? booking.user.name : booking.user_id}</td>
                <td>${booking.seat ? booking.seat.seat_number : booking.seat_id}</td>
                <td>${new Date(booking.booking_date).toLocaleDateString()}</td>
                <td>${Number(booking.payment_amount).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</td>
                <td>${booking.paid_at ? new Date(booking.paid_at).toLocaleString() : '-'}</td>
            `;
            tbody.appendChild(row);
        });
    }

    async function loadBookings() {
        const { body } = await requestJson('GET', '/api/bookings');
        const tbody = document.querySelector('#bookings-table tbody');
        tbody.innerHTML = '';

        const userBookings = (body.data || []).filter(booking => booking.user_id === auth.currentUser.id);

        if (userBookings.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8">Belum ada reservasi.</td></tr>';
            return false;
        }

        userBookings.forEach(booking => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${booking.id}</td>
                <td>${booking.train ? booking.train.name : booking.train_id}</td>
                <td>${booking.seat ? booking.seat.seat_number : booking.seat_id}</td>
                <td>${new Date(booking.booking_date).toLocaleDateString()}</td>
                <td>${formatStatus(booking.status)}</td>
                <td>${Number(booking.payment_amount).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</td>
                <td>${formatPaymentStatus(booking.payment_status)}</td>
                <td class="payment-countdown" data-expiry="${booking.payment_expires_at || ''}">${booking.payment_status !== 'paid' && booking.payment_expires_at ? formatCountdown(booking.payment_expires_at) : '-'}</td>
                <td class="actions">
                    ${booking.status === 'confirmed' && booking.payment_status === 'unpaid' ? `<button class="secondary" onclick="payBooking(${booking.id})">Bayar</button>` : ''}
                </td>
            `;
            tbody.appendChild(row);
        });

        return userBookings.length > 0;
    }

    function selectSeat(seat) {
        // fetch latest seat data to ensure price/is_reserved are authoritative
        (async () => {
            try {
                const { status, body } = await requestJson('GET', `/api/seats/${seat.id}`);
                if (status === 200 && body && body.data) {
                    selectedSeat = body.data;
                } else {
                    selectedSeat = seat;
                }
            } catch (e) {
                selectedSeat = seat;
            }
            updateSeatInfo();
        })();
    }

    function selectTrain(train) {
        selectedTrainId = train.id;
        selectedTrain = train;
        selectedSeat = null;
        document.getElementById('admin-selected-train-name').textContent = train.name;
        const schedule = document.getElementById('selected-train-schedule');
        if (schedule) {
            schedule.innerHTML = `Nama KA: <strong>${train.name}</strong><br>Rute: <strong>${train.route}</strong><br>Berangkat: <strong>${formatDateTime(train.departure_time)}</strong><br>Tiba: <strong>${formatDateTime(train.arrival_time)}</strong>`;
        }
        document.getElementById('seat-card').style.display = 'none';
        document.getElementById('admin-train-bookings-card').style.display = 'none';

        if (auth.currentUser && auth.currentUser.role === 'admin') {
            document.getElementById('admin-train-bookings-card').style.display = 'block';
            loadAdminTrainBookings(train.id);
            return;
        }

        document.getElementById('seat-card').style.display = 'block';
        document.getElementById('seat-info').textContent = 'Klik kursi untuk melihat detail.';
        loadSeats(train.id);
    }

    function updateSeatInfo() {
        const info = document.getElementById('seat-info');
        if (!info) return;
        if (!selectedSeat) {
            const trainInfo = selectedTrain ? `
                <p>Kereta: <strong>${selectedTrain.name}</strong></p>
                <p>Rute: <strong>${selectedTrain.route}</strong></p>
                <p>Berangkat: <strong>${formatDateTime(selectedTrain.departure_time)}</strong></p>
                <p>Tiba: <strong>${formatDateTime(selectedTrain.arrival_time)}</strong></p>
            ` : '<p>Pilih kursi untuk melihat detail.</p>';
            info.innerHTML = selectedTrain ? `<div>${trainInfo}</div>` : 'Pilih kursi untuk melihat detail.';
            return;
        }

        const seatPrice = getSeatPrice(selectedSeat);
        const status = selectedSeat.is_reserved ? 'Sudah terreservasi' : 'Tersedia';
        const seatDetails = `
            <h4>${selectedSeat.seat_number}</h4>
            <p>Status: <strong>${status}</strong></p>
            <p>Kereta: <strong>${selectedTrain ? selectedTrain.name : '-'}</strong></p>
            <p>Rute: <strong>${selectedTrain ? selectedTrain.route : '-'}</strong></p>
            <p>Berangkat: <strong>${selectedTrain ? formatDateTime(selectedTrain.departure_time) : '-'}</strong></p>
            <p>Tiba: <strong>${selectedTrain ? formatDateTime(selectedTrain.arrival_time) : '-'}</strong></p>
            <p>Harga: <strong>${Number(seatPrice).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</strong></p>
        `;

        let actionButton = '';
        if (auth.currentUser) {
            if (auth.currentUser.role === 'passenger') {
                if (!selectedSeat.is_reserved) {
                    actionButton = '<button id="seat-action-button">Pesan Kursi</button>';
                } else {
                    actionButton = '<button class="secondary" disabled>Sudah dipesan</button>';
                }
            } else if (auth.currentUser.role === 'admin') {
                if (selectedSeat.is_reserved) {
                    actionButton = '<button id="seat-action-button">Batalkan Reservasi</button>';
                } else {
                    actionButton = '<button id="seat-action-button">ACC Kursi</button>';
                }
            }
        }

        info.innerHTML = `<div>${seatDetails}</div>${actionButton}`;
        const actionButtonElement = document.getElementById('seat-action-button');
        if (actionButtonElement) {
            actionButtonElement.addEventListener('click', async () => {
                if (auth.currentUser.role === 'passenger') {
                    await reserveSeat(selectedSeat.id);
                } else {
                    if (selectedSeat.is_reserved) {
                        await releaseSeat(selectedSeat.id);
                    } else {
                        await reserveSeat(selectedSeat.id);
                    }
                }
            });
        }
    }

    async function loadSeats(trainId) {
        const { status, body } = await requestJson('GET', `/api/seats?train_id=${trainId}`);
        if (status !== 200) {
            showMessage(body.message || 'Gagal memuat kursi', 'error', 'seat-message');
            return;
        }
        const seats = body.data || [];
        selectedSeat = null;
        updateSeatInfo();
        renderSeatGrid(seats);
    }

    function getSeatPrice(seat) {
        const price = Number(seat.price) || 0;
        if (price > 0) {
            return price;
        }
        if (seat.train && seat.train.route) {
            const route = seat.train.route.toLowerCase();
            if (route.includes('jakarta') && route.includes('bandung')) return 150000;
            if (route.includes('jakarta') && route.includes('surabaya')) return 650000;
            if (route.includes('jakarta') && route.includes('yogyakarta')) return 250000;
            if (route.includes('bandung') && route.includes('yogyakarta')) return 320000;
            if (route.includes('bandung') && route.includes('surabaya')) return 500000;
        }

        return 200000;
    }

    async function loadAdminTrainBookings(trainId) {
        const { body } = await requestJson('GET', '/api/bookings');
        const tbody = document.querySelector('#admin-train-bookings-table tbody');
        tbody.innerHTML = '';

        const trainBookings = body.data.filter(booking => Number(booking.train_id) === Number(trainId));

        if (trainBookings.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8">Belum ada reservasi untuk kereta ini.</td></tr>';
            return;
        }

        trainBookings.forEach((booking, idx) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${idx + 1}</td>
                <td>${booking.user ? booking.user.name : booking.user_id}</td>
                <td>${booking.seat ? booking.seat.seat_number : booking.seat_id}</td>
                <td>${new Date(booking.booking_date).toLocaleDateString()}</td>
                <td>${formatStatus(booking.status)}</td>
                <td>${Number(booking.payment_amount).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</td>
                <td>${formatPaymentStatus(booking.payment_status)}</td>
                <td>${booking.status === 'pending' ? `<button class="secondary" onclick="confirmBooking(${booking.id})">ACC</button>` : ''}</td>
            `;
            tbody.appendChild(row);
        });
    }

    function renderSeatGrid(seats) {
        const grid = document.getElementById('seat-grid');
        grid.innerHTML = '';
        if (seats.length === 0) {
            grid.innerHTML = '<div class="note">Belum ada kursi untuk kereta ini.</div>';
            return;
        }
        seats.forEach(seat => {
            const box = document.createElement('div');
            box.className = 'seat-box';
            if (seat.is_reserved) box.classList.add('reserved');
            if (selectedSeat && selectedSeat.id === seat.id) box.classList.add('selected');
            box.innerHTML = `<strong>${seat.seat_number}</strong><div style="font-size:12px; margin-top:4px;">${seat.is_reserved ? 'Reserved' : 'Available'}</div>`;
            box.onclick = () => selectSeat(seat);
            grid.appendChild(box);
        });
    }

    function updateRegisterRoleAccess() {
        const roleSelect = document.getElementById('register-role');
        if (!roleSelect) return;

        if (auth.currentUser && auth.currentUser.role === 'admin') {
            roleSelect.disabled = false;
        } else {
            roleSelect.disabled = true;
            roleSelect.value = 'passenger';
        }
    }

    async function updateDashboardState() {
        const loginCard = document.getElementById('login-card');
        const bookingCard = document.getElementById('booking-card');
        const trainCard = document.getElementById('train-card');
        const adminBookingCard = document.getElementById('admin-booking-card');
        const messageBox = document.getElementById('dashboard-message');
        if (!auth.currentUser) {
            loginCard.style.display = 'block';
            bookingCard.style.display = 'none';
            trainCard.style.display = 'none';
            document.getElementById('seat-card').style.display = 'none';
            if (adminBookingCard) adminBookingCard.style.display = 'none';
            messageBox.innerHTML = '<div class="alert warning">Silakan login atau register untuk melihat kereta dan kursi.</div>';
            return;
        }

        loginCard.style.display = 'none';
        // hide passenger personal booking card for admin users
        bookingCard.style.display = auth.currentUser.role === 'admin' ? 'none' : 'block';
        trainCard.style.display = 'block';
        document.getElementById('seat-card').style.display = 'none';
        document.getElementById('admin-train-bookings-card').style.display = 'none';
        document.getElementById('admin-paid-card').style.display = 'none';

        if (auth.currentUser.role === 'admin') {
            adminBookingCard.style.display = 'block';
            document.getElementById('admin-paid-card').style.display = 'block';
            loadAdminBookings();
            loadAdminPaidUsers();

            // keep paid-monitor updated every 10 seconds
            if (!window.__adminPaidInterval) {
                window.__adminPaidInterval = setInterval(loadAdminPaidUsers, 10000);
            }
        } else {
            adminBookingCard.style.display = 'none';
            if (window.__adminPaidInterval) {
                clearInterval(window.__adminPaidInterval);
                window.__adminPaidInterval = null;
            }
        }

        const hasBookings = auth.currentUser.role === 'passenger' ? await loadBookings() : false;

        if (auth.currentUser.role === 'passenger' && hasBookings) {
            messageBox.innerHTML = `<div class="alert success">Login sebagai <strong>${auth.currentUser.name}</strong> (${auth.currentUser.role}). Reservasi Anda sudah tercatat, tetap bisa lihat kereta dan kursi.</div>`;
        } else {
            messageBox.innerHTML = `<div class="alert success">Login sebagai <strong>${auth.currentUser.name}</strong> (${auth.currentUser.role}). Pilih kereta untuk melihat kursi.</div>`;
        }

        loadTrains();
        if (selectedTrain) {
            selectTrain(selectedTrain);
        }

        updateRegisterRoleAccess();
    }

    async function reserveSeat(id) {
        const bookingPayload = {
            user_id: auth.currentUser.id,
            train_id: selectedTrainId,
            seat_id: id,
            booking_date: new Date().toISOString().split('T')[0], // Today's date
        };
        const bookingResponse = await requestJson('POST', '/api/bookings', bookingPayload);
        if (bookingResponse.status === 201) {
            showMessage('Kursi berhasil direservasi dan booking dibuat', 'success', 'seat-message');
            if (selectedSeat && selectedSeat.id === id) {
                selectedSeat.is_reserved = true;
                updateSeatInfo();
            }
            await loadSeats(selectedTrainId);
            await updateDashboardState(); // Reload to show bookings
        } else {
            showMessage(bookingResponse.body.message || 'Gagal reservasi kursi', 'error', 'seat-message');
        }
    }

    async function releaseSeat(id) {
        const { status, body } = await requestJson('POST', `/api/seats/${id}/release`);
        if (status === 200) {
            showMessage('Reservasi kursi dibatalkan', 'success', 'seat-message');
            if (selectedSeat && selectedSeat.id === id) {
                selectedSeat.is_reserved = false;
                updateSeatInfo();
            }
            await loadSeats(selectedTrainId);
            await updateDashboardState();
        } else {
            showMessage(body.message || 'Gagal batalkan reservasi kursi', 'error', 'seat-message');
        }
    }

    async function confirmBooking(bookingId) {
        const { status, body } = await requestJson('POST', `/api/bookings/${bookingId}/confirm`);
        if (status === 200) {
            showMessage('Reservasi berhasil di-ACC', 'success', 'dashboard-message');
            loadAdminBookings();
            loadBookings(); // Reload user bookings if needed
        } else {
            showMessage(body.message || 'Gagal ACC reservasi', 'error', 'dashboard-message');
        }
    }

    async function payBooking(bookingId) {
        const payload = { payment_method: 'card' };
        const { status, body } = await requestJson('POST', `/api/bookings/${bookingId}/pay`, payload);
        if (status === 200) {
            showMessage('Pembayaran berhasil diproses', 'success', 'dashboard-message');
            loadBookings();
            loadAdminBookings();
        } else {
            showMessage(body.message || 'Gagal memproses pembayaran', 'error', 'dashboard-message');
            loadBookings();
            loadAdminBookings();
        }
    }

    function formatCountdown(expiry) {
        if (!expiry) {
            return '-';
        }

        const end = new Date(expiry).getTime();
        const now = Date.now();
        const diff = end - now;

        if (diff <= 0) {
            return 'Expired';
        }

        const minutes = Math.floor(diff / 1000 / 60);
        const seconds = Math.floor((diff / 1000) % 60);
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    function updateCountdownTimers() {
        document.querySelectorAll('.payment-countdown').forEach(cell => {
            const expiry = cell.getAttribute('data-expiry');
            if (!expiry) {
                return;
            }
            cell.textContent = formatCountdown(expiry);
        });
    }

    setInterval(updateCountdownTimers, 1000);

    document.getElementById('login-user').addEventListener('click', async () => {
        const payload = {
            email: document.getElementById('login-email').value,
            password: document.getElementById('login-password').value,
        };
        const { status, body } = await requestJson('POST', '/api/users/login', payload);
        if (status === 200) {
            setCurrentUser(body.data);
            await updateDashboardState();
        } else {
            showMessage(body.message || 'Login gagal', 'error');
        }
    });

    document.getElementById('register-user').addEventListener('click', async () => {
        const payload = {
            name: document.getElementById('register-name').value,
            email: document.getElementById('register-email').value,
            password: document.getElementById('register-password').value,
        };
        const { status, body } = await requestJson('POST', '/api/users', payload);
        if (status === 201) {
            showMessage('Akun berhasil dibuat', 'success');
            if (!auth.currentUser) {
                setCurrentUser(body.data);
                await updateDashboardState();
            }
        } else {
            showMessage(body.message || 'Registrasi gagal', 'error');
        }
    });

    document.addEventListener('DOMContentLoaded', updateDashboardState);
</script>
@endpush
