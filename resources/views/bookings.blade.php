@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Bookings</h1>
        <div id="message-container"></div>
        <div class="card" id="booking-admin-message"></div>
        <div class="card" id="booking-form">
            <h2>Create Booking</h2>
            <div class="grid grid-2">
                <div id="booking-user-row">
                    <label for="booking-user">User</label>
                    <select id="booking-user"></select>
                </div>
                <div>
                    <label for="booking-train">Train</label>
                    <select id="booking-train"></select>
                </div>
                <div>
                    <label for="booking-seat">Seat</label>
                    <select id="booking-seat"></select>
                </div>
                <div>
                    <label for="booking-date">Booking Date</label>
                    <input id="booking-date" type="date">
                </div>
            </div>
            <button id="create-booking">Create Booking</button>
        </div>
        <div class="card">
            <h2>Booking List</h2>
            <table id="bookings-table">
                <thead>
                    <tr><th>ID</th><th>User</th><th>Train</th><th>Seat</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function updateBookingAccess() {
        const form = document.getElementById('booking-form');
        const message = document.getElementById('booking-admin-message');
        const userRow = document.getElementById('booking-user-row');

        if (!auth.currentUser) {
            form.style.display = 'none';
            message.innerHTML = '<strong>Login required:</strong> login as passenger or admin to create bookings.';
            return;
        }

        form.style.display = 'block';
        message.innerHTML = '';

        if (auth.currentUser.role === 'passenger') {
            userRow.style.display = 'none';
        } else {
            userRow.style.display = 'block';
        }
    }

    async function loadBookingOptions() {
        const [users, trains, seats] = await Promise.all([
            requestJson('GET', '/api/users'),
            requestJson('GET', '/api/trains'),
            requestJson('GET', '/api/seats'),
        ]);

        const userSelect = document.getElementById('booking-user');
        const trainSelect = document.getElementById('booking-train');
        const seatSelect = document.getElementById('booking-seat');
        userSelect.innerHTML = '<option value="">Select user</option>';
        trainSelect.innerHTML = '<option value="">Select train</option>';
        seatSelect.innerHTML = '<option value="">Select seat</option>';

        if (users.body.data) {
            users.body.data.forEach(user => {
                const opt = document.createElement('option');
                opt.value = user.id;
                opt.textContent = `${user.name} (${user.role})`;
                userSelect.appendChild(opt);
            });
        }

        if (trains.body.data) {
            trains.body.data.forEach(train => {
                const opt = document.createElement('option');
                opt.value = train.id;
                opt.textContent = `${train.name} (${train.route})`;
                trainSelect.appendChild(opt);
            });
        }

        if (seats.body.data) {
            seats.body.data.forEach(seat => {
                const opt = document.createElement('option');
                opt.value = seat.id;
                opt.textContent = `${seat.seat_number} - ${seat.train ? seat.train.name : 'N/A'}`;
                seatSelect.appendChild(opt);
            });
        }
    }

    async function loadBookings() {
        const { body } = await requestJson('GET', '/api/bookings');
        const tbody = document.querySelector('#bookings-table tbody');
        tbody.innerHTML = '';

        if (body.data) {
            body.data.forEach(booking => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${booking.id}</td>
                    <td>${booking.user ? booking.user.name : booking.user_id}</td>
                    <td>${booking.train ? booking.train.name : booking.train_id}</td>
                    <td>${booking.seat ? booking.seat.seat_number : booking.seat_id}</td>
                    <td>${new Date(booking.booking_date).toLocaleDateString()}</td>
                    <td>${formatStatus(booking.status)}</td>
                    <td class="actions">
                        <button onclick="cancelBooking(${booking.id})" class="secondary">Cancel</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    }

    function showMessage(message, type = 'success') {
        const container = document.getElementById('message-container');
        container.innerHTML = `<div class="alert ${type}">${message}</div>`;
        setTimeout(() => { container.innerHTML = ''; }, 5000);
    }

    document.getElementById('create-booking').addEventListener('click', async () => {
        const payload = {
            user_id: auth.currentUser && auth.currentUser.role === 'passenger' ? auth.currentUser.id : Number(document.getElementById('booking-user').value),
            train_id: Number(document.getElementById('booking-train').value),
            seat_id: Number(document.getElementById('booking-seat').value),
            booking_date: document.getElementById('booking-date').value,
        };

        const { status, body } = await requestJson('POST', '/api/bookings', payload);
        if (status === 201) {
            showMessage('Booking created successfully');
            loadBookings();
        } else {
            showMessage(body.message || 'Failed to create booking', 'error');
        }
    });

    async function cancelBooking(id) {
        const { status, body } = await requestJson('DELETE', `/api/bookings/${id}`);
        if (status === 200) {
            showMessage('Booking cancelled successfully');
            loadBookings();
        } else {
            showMessage(body.message || 'Failed to cancel booking', 'error');
        }
    }

    loadBookingOptions();
    loadBookings();
    updateBookingAccess();
</script>
@endpush
