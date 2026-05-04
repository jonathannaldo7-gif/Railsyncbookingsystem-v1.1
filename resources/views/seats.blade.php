@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Seats</h1>
        <div id="message-container"></div>
        <div class="card" id="seat-admin-message"></div>
        <div class="card" id="seat-form">
            <h2>Add Seat</h2>
            <div class="grid grid-2">
                <div>
                    <label for="seat-train">Train</label>
                    <select id="seat-train"></select>
                </div>
                <div>
                    <label for="seat-number">Seat Number</label>
                    <input id="seat-number" type="text" placeholder="A1">
                </div>
            </div>
            <button id="create-seat">Create Seat</button>
        </div>
        <div class="card">
            <h2>Seat List</h2>
            <table id="seats-table">
                <thead>
                    <tr><th>ID</th><th>Train</th><th>Seat</th><th>Reserved</th><th>Actions</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    async function loadSeatOptions() {
        const { body } = await requestJson('GET', '/api/trains');
        const select = document.getElementById('seat-train');
        select.innerHTML = '<option value="">Select train</option>';
        if (body.data) {
            body.data.forEach(train => {
                const option = document.createElement('option');
                option.value = train.id;
                option.textContent = `${train.name} (${train.route})`;
                select.appendChild(option);
            });
        }
    }

    async function loadSeats() {
        const { body } = await requestJson('GET', '/api/seats');
        const tbody = document.querySelector('#seats-table tbody');
        tbody.innerHTML = '';
        if (body.data) {
            body.data.forEach(seat => {
                const status = seat.is_reserved ? 'Yes' : 'No';
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${seat.id}</td>
                    <td>${seat.train ? seat.train.name : 'N/A'}</td>
                    <td>${seat.seat_number}</td>
                    <td>${status}</td>
                    <td class="actions">
                        <button onclick="reserveSeat(${seat.id})" class="secondary">Reserve</button>
                        <button onclick="releaseSeat(${seat.id})" class="secondary">Release</button>
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

    function updateSeatAccess() {
        const form = document.getElementById('seat-form');
        const message = document.getElementById('seat-admin-message');
        if (auth.currentUser && auth.currentUser.role === 'admin') {
            form.style.display = 'block';
            message.innerHTML = '';
        } else {
            form.style.display = 'none';
            message.innerHTML = '<strong>Admin only:</strong> login as admin to add seats.';
        }
    }

    document.getElementById('create-seat').addEventListener('click', async () => {
        const payload = {
            train_id: Number(document.getElementById('seat-train').value),
            seat_number: document.getElementById('seat-number').value,
        };
        const { status, body } = await requestJson('POST', '/api/seats', payload);
        if (status === 201) {
            showMessage('Seat created successfully');
            loadSeats();
        } else {
            showMessage(body.message || 'Failed to create seat', 'error');
        }
    });

    updateSeatAccess();

    async function reserveSeat(id) {
        const { status, body } = await requestJson('POST', `/api/seats/${id}/reserve`);
        if (status === 200) {
            showMessage('Seat reserved successfully');
            loadSeats();
        } else {
            showMessage(body.message || 'Failed to reserve seat', 'error');
        }
    }

    async function releaseSeat(id) {
        const { status, body } = await requestJson('POST', `/api/seats/${id}/release`);
        if (status === 200) {
            showMessage('Seat released successfully');
            loadSeats();
        } else {
            showMessage(body.message || 'Failed to release seat', 'error');
        }
    }

    loadSeatOptions();
    loadSeats();
</script>
@endpush
