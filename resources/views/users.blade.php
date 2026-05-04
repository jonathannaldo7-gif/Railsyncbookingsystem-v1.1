@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Users</h1>
        <div id="message-container"></div>
        <div class="grid grid-2">
            <div class="card">
                <h2>Create Account</h2>
                <label for="user-name">Name</label>
                <input id="user-name" type="text" placeholder="Full name">
                <label for="user-email">Email</label>
                <input id="user-email" type="email" placeholder="user@example.com">
                <label for="user-password">Password</label>
                <input id="user-password" type="password" placeholder="Minimum 8 characters">
                <label for="user-role">Role</label>
                <select id="user-role">
                    <option value="passenger">Passenger</option>
                    <option value="admin">Admin</option>
                </select>
                <button id="create-user">Create User</button>
                <p class="note">Default new accounts are created as passenger. Admin role is only enabled after login as admin.</p>
            </div>
            <div class="card">
                <h2>Login</h2>
                <label for="login-email">Email</label>
                <input id="login-email" type="email" placeholder="user@example.com">
                <label for="login-password">Password</label>
                <input id="login-password" type="password" placeholder="Password">
                <button id="login-user" class="secondary">Login</button>
            </div>
        </div>
        <div class="card">
            <h2>User List</h2>
            <table id="users-table">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function updateUserForm() {
        const roleSelect = document.getElementById('user-role');
        if (auth.currentUser && auth.currentUser.role === 'admin') {
            roleSelect.disabled = false;
        } else {
            roleSelect.value = 'passenger';
            roleSelect.disabled = true;
        }
    }

    async function loadUsers() {
        const { body } = await requestJson('GET', '/api/users');
        const tbody = document.querySelector('#users-table tbody');
        tbody.innerHTML = '';
        if (body.data) {
            body.data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${user.id}</td><td>${user.name}</td><td>${user.email}</td><td>${user.role}</td>`;
                tbody.appendChild(row);
            });
        }
    }

    function showMessage(message, type = 'success') {
        const container = document.getElementById('message-container');
        container.innerHTML = `<div class="alert ${type}">${message}</div>`;
        setTimeout(() => { container.innerHTML = ''; }, 5000);
    }

    document.getElementById('create-user').addEventListener('click', async () => {
        const payload = {
            name: document.getElementById('user-name').value,
            email: document.getElementById('user-email').value,
            password: document.getElementById('user-password').value,
            role: document.getElementById('user-role').value,
        };
        const { status, body } = await requestJson('POST', '/api/users', payload);
        if (status === 201) {
            showMessage('User created successfully');
            loadUsers();
        } else {
            showMessage(body.message || 'Failed to save user', 'error');
        }
    });

    document.getElementById('login-user').addEventListener('click', async () => {
        const payload = {
            email: document.getElementById('login-email').value,
            password: document.getElementById('login-password').value,
        };
        const { status, body } = await requestJson('POST', '/api/users/login', payload);
        if (status === 200) {
            setCurrentUser(body.data);
            showMessage('Login success for ' + body.data.name);
            updateUserForm();
            loadUsers();
        } else {
            showMessage(body.message || 'Login failed', 'error');
        }
    });

    loadUsers();
    updateUserForm();
</script>
@endpush
