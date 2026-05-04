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
                <label for="register-role">Role</label>
                <select id="register-role" disabled>
                    <option value="passenger">Passenger</option>
                    <option value="admin">Admin</option>
                </select>
                <button id="register-user">Register</button>
                <p class="note">Buat akun baru untuk masuk dan melakukan reservasi.</p>
            </div>
        </div>
        <p class="note".</p>
    </div>

    <div class="card" id="booking-card" style="display: none;">
        <h2>Reservasi Saya</h2>
        <table id="bookings-table">
            <thead>
                <tr><th>ID</th><th>Kereta</th><th>Kursi</th><th>Tanggal</th><th>Status</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card" id="admin-booking-card" style="display: none;">
        <h2>ACC Reservasi (Admin)</h2>
        <table id="admin-bookings-table">
            <thead>
                <tr><th>ID</th><th>Penumpang</th><th>Kereta</th><th>Kursi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card" id="train-card" style="display: none;">
        <h2>Daftar Kereta</h2>
        <table id="trains-table">
            <thead>
                <tr><th>ID</th><th>Nama</th><th>Rute</th><th>Berangkat</th><th>Tiba</th><th>Aksi</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card" id="admin-train-bookings-card" style="display: none;">
        <h2>Reservasi Kereta: <span id="admin-selected-train-name"></span></h2>
        <table id="admin-train-bookings-table">
            <thead>
                <tr><th>ID</th><th>Penumpang</th><th>Kursi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card" id="seat-card" style="display: none;">
        <h2>Tempat Duduk untuk Kereta: <span id="selected-train-name"></span></h2>
        <div id="seat-message"></div>
        <div class="seat-card-info">
            <div id="seat-info">Klik kursi untuk melihat detail.</div>
        </div>
        <div class="seat-grid" id="seat-grid"></div>
    </div>
@endsection

@push('scripts')
<script>
    let selectedTrainId = null;
    let selectedTrainName = '';
    let selectedSeat = null;

    function showMessage(message, type = 'success', containerId = 'message-container') {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = `<div class="alert ${type}">${message}</div>`;
        setTimeout(() => { container.innerHTML = ''; }, 5000);
    }

    async function loadTrains() {
        const { body } = await requestJson('GET', '/api/trains');
        const tbody = document.querySelector('#trains-table tbody');
        tbody.innerHTML = '';
        if (!body.data || body.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">Belum ada kereta.</td></tr>';
            return;
        }
        body.data.forEach(train => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${train.id}</td>
                <td>${train.name}</td>
                <td>${train.route}</td>
                <td>${new Date(train.departure_time).toLocaleString()}</td>
                <td>${new Date(train.arrival_time).toLocaleString()}</td>
                <td><button class="secondary" onclick="selectTrain(${train.id}, '${train.name.replace(/'/g, "\\'")}')">Pilih</button></td>
            `;
            tbody.appendChild(row);
        });
    }

    async function loadAdminBookings() {
        const { body } = await requestJson('GET', '/api/bookings');
        const tbody = document.querySelector('#admin-bookings-table tbody');
        tbody.innerHTML = '';

        const pendingBookings = body.data.filter(booking => booking.status === 'pending');

        if (pendingBookings.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7">Tidak ada reservasi pending.</td></tr>';
            return;
        }

        pendingBookings.forEach(booking => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${booking.id}</td>
                <td>${booking.user ? booking.user.name : booking.user_id}</td>
                <td>${booking.train ? booking.train.name : booking.train_id}</td>
                <td>${booking.seat ? booking.seat.seat_number : booking.seat_id}</td>
                <td>${new Date(booking.booking_date).toLocaleDateString()}</td>
                <td>${formatStatus(booking.status)}</td>
                <td><button class="secondary" onclick="confirmBooking(${booking.id})">ACC</button></td>
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
            tbody.innerHTML = '<tr><td colspan="5">Belum ada reservasi.</td></tr>';
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
            `;
            tbody.appendChild(row);
        });

        return userBookings.length > 0;
    }

    function selectSeat(seat) {
        selectedSeat = seat;
        updateSeatInfo();
    }

    function selectTrain(id, name) {
        selectedTrainId = id;
        selectedTrainName = name;
        selectedSeat = null;
        document.getElementById('selected-train-name').textContent = name;
        document.getElementById('admin-selected-train-name').textContent = name;
        document.getElementById('seat-card').style.display = 'none';
        document.getElementById('admin-train-bookings-card').style.display = 'none';

        if (auth.currentUser && auth.currentUser.role === 'admin') {
            document.getElementById('admin-train-bookings-card').style.display = 'block';
            loadAdminTrainBookings(id);
            return;
        }

        document.getElementById('seat-card').style.display = 'block';
        document.getElementById('seat-info').textContent = 'Klik kursi untuk melihat detail.';
        loadSeats(id);
    }

    function updateSeatInfo() {
        const info = document.getElementById('seat-info');
        if (!info) return;
        if (!selectedSeat) {
            info.innerHTML = 'Pilih kursi untuk melihat detail.';
            return;
        }

        const status = selectedSeat.is_reserved ? 'Sudah terreservasi' : 'Tersedia';
        const seatDetails = `
            <h4>${selectedSeat.seat_number}</h4>
            <p>Status: <strong>${status}</strong></p>
            <p>Kereta: <strong>${selectedTrainName}</strong></p>
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
        const { body } = await requestJson('GET', `/api/seats?train_id=${trainId}`);
        const seats = body.data || [];
        selectedSeat = null;
        updateSeatInfo();
        renderSeatGrid(seats);
    }

    async function loadAdminTrainBookings(trainId) {
        const { body } = await requestJson('GET', '/api/bookings');
        const tbody = document.querySelector('#admin-train-bookings-table tbody');
        tbody.innerHTML = '';

        const trainBookings = body.data.filter(booking => Number(booking.train_id) === Number(trainId));

        if (trainBookings.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">Belum ada reservasi untuk kereta ini.</td></tr>';
            return;
        }

        trainBookings.forEach(booking => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${booking.id}</td>
                <td>${booking.user ? booking.user.name : booking.user_id}</td>
                <td>${booking.seat ? booking.seat.seat_number : booking.seat_id}</td>
                <td>${new Date(booking.booking_date).toLocaleDateString()}</td>
                <td>${formatStatus(booking.status)}</td>
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
        bookingCard.style.display = 'block';
        trainCard.style.display = 'block';
        document.getElementById('seat-card').style.display = 'none';
        document.getElementById('admin-train-bookings-card').style.display = 'none';

        if (auth.currentUser.role === 'admin') {
            adminBookingCard.style.display = 'block';
            loadAdminBookings();
        } else {
            adminBookingCard.style.display = 'none';
        }

        const hasBookings = auth.currentUser.role === 'passenger' ? await loadBookings() : false;

        if (auth.currentUser.role === 'passenger' && hasBookings) {
            messageBox.innerHTML = `<div class="alert success">Login sebagai <strong>${auth.currentUser.name}</strong> (${auth.currentUser.role}). Reservasi Anda sudah tercatat, tetap bisa lihat kereta dan kursi.</div>`;
        } else {
            messageBox.innerHTML = `<div class="alert success">Login sebagai <strong>${auth.currentUser.name}</strong> (${auth.currentUser.role}). Pilih kereta untuk melihat kursi.</div>`;
        }

        loadTrains();
        if (selectedTrainId) {
            selectTrain(selectedTrainId, selectedTrainName);
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
            loadSeats(selectedTrainId);
            updateDashboardState(); // Reload to show bookings
        } else {
            showMessage(bookingResponse.body.message || 'Gagal reservasi kursi', 'error', 'seat-message');
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
            role: document.getElementById('register-role').value,
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
