<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railsync Booking System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f5f7; color: #1f2937; }
        .app-nav { background: #111827; color: #f9fafb; padding: 16px; display: flex; gap: 16px; flex-wrap: wrap; }
        .app-nav a { color: #f9fafb; text-decoration: none; padding: 8px 12px; border-radius: 6px; transition: background .2s ease; }
        .app-nav a:hover { background: rgba(255,255,255,0.08); }
        .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; box-shadow: 0 4px 18px rgba(15,23,42,0.06); margin-bottom: 20px; }
        h1, h2, h3 { margin-top: 0; }
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        label { display: block; font-weight: 600; margin-bottom: 6px; }
        input, select, button { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        button { cursor: pointer; background: #2563eb; color: #ffffff; border: none; }
        button.secondary { background: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .table-wrapper { width: 100%; overflow-x: auto; }
        .table-wrapper table { min-width: 850px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #e5e7eb; text-align: left; white-space: nowrap; }
        th { background: #f9fafb; font-weight: 700; }
        td.payment-countdown, th.payment-countdown, td.actions, th.actions { min-width: 110px; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .actions button { width: auto; }
        .badge { display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge.success { background: #dcfce7; color: #166534; }
        .badge.warning { background: #fef3c7; color: #92400e; }
        .badge.danger { background: #fee2e2; color: #991b1b; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .seat-grid { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(72px, 1fr)); margin-top: 16px; }
        .seat-box { padding: 10px 8px; border: 1px solid #d1d5db; border-radius: 10px; text-align: center; cursor: pointer; background: #f9fafb; }
        .seat-box.reserved { background: #fee2e2; border-color: #fca5a5; cursor: not-allowed; }
        .seat-box.selected { background: #dbeafe; border-color: #3b82f6; }
        .seat-card-info { margin-top: 16px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 16px; background: #ffffff; }
        .ticket-card { display: grid; gap: 24px; }
        .ticket-summary, .seat-selection { display: grid; gap: 12px; }
        .ticket-summary h3, .seat-selection h3 { margin: 0; font-size: 18px; color: #111827; }
        .ticket-detail { padding: 18px; border: 1px solid #e5e7eb; border-radius: 16px; background: #f8fafc; color: #374151; line-height: 1.6; }
        .seat-grid { margin-top: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(90px, 1fr)); gap: 12px; }
        .seat-box { min-height: 80px; display: flex; align-items: center; justify-content: center; flex-direction: column; border: 1px solid #d1d5db; border-radius: 14px; padding: 12px; background: #ffffff; transition: transform .18s ease, box-shadow .18s ease; }
        .seat-box:hover:not(.reserved) { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(15,23,42,0.08); }
        .seat-box.reserved { background: #fef2f2; border-color: #fecaca; cursor: not-allowed; }
        button { transition: background .2s ease, transform .2s ease; }
        button:hover { transform: translateY(-1px); }
        button.secondary { background: #1f2937; color: #ffffff; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #e5e7eb; text-align: left; white-space: nowrap; }
        tr:hover td { background: #f9fafb; }
        .table-wrapper { width: 100%; overflow-x: auto; }
        .table-wrapper table { min-width: 820px; border-radius: 14px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #f9fafb; font-weight: 700; }
        .payment-countdown, th.payment-countdown, td.actions, th.actions { min-width: 120px; }
        .badge { padding: 8px 12px; border-radius: 999px; font-size: 12px; }
        .alert { padding: 14px 16px; border-radius: 12px; margin-bottom: 18px; }
        .alert.error { background: #fee2e2; color: #991b1b; }
        .alert.success { background: #dcfce7; color: #166534; }
        .app-auth { background: #e5e7eb; padding: 12px 16px; color: #111827; border-bottom: 1px solid #d1d5db; }
        .app-auth button { width: auto; display: inline-block; margin-left: 12px; }
        .note { margin-top: 16px; color: #4b5563; font-size: 14px; }
    </style>
</head>
<body>
    <nav class="app-nav">
        <a href="{{ url('/') }}">Dashboard</a>
    </nav>
    <div class="app-auth" id="auth-status"></div>
    <main class="container">
        @yield('content')
    </main>
    <script>
        const auth = {
            currentUser: JSON.parse(localStorage.getItem('railsync_current_user') || 'null'),
        };

        function setCurrentUser(user) {
            auth.currentUser = user;
            localStorage.setItem('railsync_current_user', JSON.stringify(user));
            renderAuth();
        }

        function clearCurrentUser() {
            auth.currentUser = null;
            localStorage.removeItem('railsync_current_user');
            renderAuth();
            location.reload();
        }

        function renderAuth() {
            const authStatus = document.getElementById('auth-status');
            if (!authStatus) return;
            if (auth.currentUser) {
                authStatus.innerHTML = `Logged in as <strong>${auth.currentUser.name}</strong> (<em>${auth.currentUser.role}</em>) <button onclick="clearCurrentUser()" class="secondary">Logout</button>`;
            } else {
                authStatus.innerHTML = 'No user logged in. Please login via the Users page.';
            }
        }

        async function requestJson(method, path, payload = null) {
            const options = { method, headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' } };
            if (payload) options.body = JSON.stringify(payload);
            const response = await fetch(path, options);
            const body = await response.json().catch(() => ({}));
            return { status: response.status, body };
        }

        function formatStatus(status) {
            if (status === 'confirmed') return '<span class="badge success">Confirmed</span>';
            if (status === 'pending') return '<span class="badge warning">Pending</span>';
            if (status === 'cancelled') return '<span class="badge danger">Cancelled</span>';
            return status;
        }

        function formatPaymentStatus(paymentStatus) {
            if (paymentStatus === 'paid') return '<span class="badge success">Paid</span>';
            if (paymentStatus === 'unpaid') return '<span class="badge warning">Unpaid</span>';
            return paymentStatus || '<span class="badge warning">Unpaid</span>';
        }

        document.addEventListener('DOMContentLoaded', renderAuth);
    </script>
    @stack('scripts')
</body>
</html>
