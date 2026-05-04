@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Trains</h1>
        <div id="message-container"></div>
        <div class="card" id="train-admin-message"></div>
        <div class="card" id="train-form">
            <h2>Add Train</h2>
            <div class="grid grid-2">
                <div>
                    <label for="train-name">Name</label>
                    <input id="train-name" type="text" placeholder="Train name">
                </div>
                <div>
                    <label for="train-route">Route</label>
                    <input id="train-route" type="text" placeholder="Jakarta - Bandung">
                </div>
                <div>
                    <label for="train-departure">Departure</label>
                    <input id="train-departure" type="datetime-local">
                </div>
                <div>
                    <label for="train-arrival">Arrival</label>
                    <input id="train-arrival" type="datetime-local">
                </div>
                <div>
                    <label for="train-capacity">Capacity</label>
                    <input id="train-capacity" type="number" min="1" value="100">
                </div>
            </div>
            <button id="create-train">Create Train</button>
        </div>
        <div class="card">
            <h2>Train List</h2>
            <table id="trains-table">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Route</th><th>Departure</th><th>Arrival</th><th>Capacity</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    async function loadTrains() {
        const { body } = await requestJson('GET', '/api/trains');
        const tbody = document.querySelector('#trains-table tbody');
        tbody.innerHTML = '';
        if (body.data) {
            body.data.forEach(train => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${train.id}</td>
                    <td>${train.name}</td>
                    <td>${train.route}</td>
                    <td>${new Date(train.departure_time).toLocaleString()}</td>
                    <td>${new Date(train.arrival_time).toLocaleString()}</td>
                    <td>${train.capacity}</td>
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

    function updateTrainAccess() {
        const form = document.getElementById('train-form');
        const message = document.getElementById('train-admin-message');
        if (auth.currentUser && auth.currentUser.role === 'admin') {
            form.style.display = 'block';
            message.innerHTML = '';
        } else {
            form.style.display = 'none';
            message.innerHTML = '<strong>Admin only:</strong> login as admin to add trains.';
        }
    }

    document.getElementById('create-train').addEventListener('click', async () => {
        const payload = {
            name: document.getElementById('train-name').value,
            route: document.getElementById('train-route').value,
            departure_time: document.getElementById('train-departure').value,
            arrival_time: document.getElementById('train-arrival').value,
            capacity: Number(document.getElementById('train-capacity').value),
        };
        const { status, body } = await requestJson('POST', '/api/trains', payload);
        if (status === 201) {
            showMessage('Train created successfully');
            loadTrains();
        } else {
            showMessage(body.message || 'Failed to create train', 'error');
        }
    });

    updateTrainAccess();
    loadTrains();
</script>
@endpush
