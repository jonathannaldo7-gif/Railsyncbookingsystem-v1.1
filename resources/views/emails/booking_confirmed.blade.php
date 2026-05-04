<!DOCTYPE html>
<html>
<head>
    <title>Reservasi Dikonfirmasi</title>
</head>
<body>
    <h1>Reservasi Kereta Api Dikonfirmasi</h1>
    <p>Halo {{ $booking->user->name }},</p>
    <p>Reservasi Anda telah dikonfirmasi oleh admin.</p>
    <p><strong>Detail Reservasi:</strong></p>
    <ul>
        <li>Kereta: {{ $booking->train->name }}</li>
        <li>Rute: {{ $booking->train->route }}</li>
        <li>Kursi: {{ $booking->seat->seat_number }}</li>
        <li>Tanggal Berangkat: {{ \Carbon\Carbon::parse($booking->train->departure_time)->format('d M Y H:i') }}</li>
        <li>Tanggal Tiba: {{ \Carbon\Carbon::parse($booking->train->arrival_time)->format('d M Y H:i') }}</li>
        <li>Tanggal Booking: {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</li>
    </ul>
    <p>Selamat menikmati perjalanan!</p>
    <p>Salam,<br>Railsync Booking System</p>
</body>
</html>
