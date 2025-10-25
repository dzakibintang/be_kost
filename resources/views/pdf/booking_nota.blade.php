<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Booking Kos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 40px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td, th { border: 1px solid #444; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Nota Pemesanan Kos</h2>
    <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</p>

    <table>
        <tr>
            <th>Nama Penyewa</th>
            <td>{{ $booking->user->name }}</td>
        </tr>
        <tr>
            <th>Nama Kos</th>
            <td>{{ $booking->kos->name }}</td>
        </tr>
        <tr>
            <th>Alamat Kos</th>
            <td>{{ $booking->kos->address }}</td>
        </tr>
        <tr>
            <th>Tanggal Mulai</th>
            <td>{{ $booking->start_date }}</td>
        </tr>
        <tr>
            <th>Tanggal Selesai</th>
            <td>{{ $booking->end_date }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($booking->status) }}</td>
        </tr>
    </table>

    <p style="margin-top: 40px;">Terima kasih telah melakukan pemesanan di Kost Hunter.</p>
</body>
</html>
