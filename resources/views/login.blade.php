@extends('layouts.app')

@section('content')
    <div class="card" style="max-width: 760px; margin: 0 auto;">
        <h1>Login / Register</h1>
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
                <p class="note">Akun baru akan disimpan dengan role di database. Hanya admin yang dapat membuat admin jika sudah login.</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showMessage(message, type = 'success') {
        const container = document.getElementById('message-container');
        container.innerHTML = `<div class="alert ${type}">${message}</div>`;
        setTimeout(() => { container.innerHTML = ''; }, 5000);
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

    document.getElementById('login-user').addEventListener('click', async () => {
        const payload = {
            email: document.getElementById('login-email').value,
            password: document.getElementById('login-password').value,
        };

        const { status, body } = await requestJson('POST', '/api/users/login', payload);
        if (status === 200) {
            setCurrentUser(body.data);
            showMessage('Login success for ' + body.data.name);
            updateRegisterRoleAccess();
            window.location.href = '/';
        } else {
            showMessage(body.message || 'Login failed', 'error');
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
                window.location.href = '/';
            }
        } else {
            showMessage(body.message || 'Registrasi gagal', 'error');
        }
    });

    document.addEventListener('DOMContentLoaded', updateRegisterRoleAccess);
</script>
@endpush
