<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Kos;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf; // <--- tambahkan ini

class BookingController extends Controller
{
    // ✅ Society buat booking baru
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $request->validate([
                'kos_id' => 'required|exists:kos,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date'
            ]);

            $booking = Booking::create([
                'kos_id' => $request->kos_id,
                'user_id' => $user->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Booking berhasil dibuat, menunggu persetujuan owner',
                'data' => $booking
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Society lihat semua booking miliknya
    public function myBookings()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $bookings = Booking::with('kos:id,name,address')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Daftar booking Anda',
                'total' => $bookings->count(),
                'data' => $bookings
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Owner lihat semua booking di kos miliknya
    public function listByOwner()
    {
        try {
            $owner = JWTAuth::parseToken()->authenticate();

            // Cek kolom pemilik kos
            $ownerColumn = null;
            foreach (['owner_id', 'admin_id', 'user_id'] as $col) {
                if (Schema::hasColumn('kos', $col)) {
                    $ownerColumn = $col;
                    break;
                }
            }

            if (!$ownerColumn) {
                return response()->json([
                    'message' => 'Kolom pemilik kos tidak ditemukan di tabel kos'
                ], 500);
            }

            $bookings = Booking::with(['user:id,name', 'kos:id,name'])
                ->whereHas('kos', function ($q) use ($owner, $ownerColumn) {
                    $q->where($ownerColumn, $owner->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Daftar booking di kos Anda',
                'total' => $bookings->count(),
                'data' => $bookings
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data booking owner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Owner ubah status booking (accept / reject)
    public function updateStatus(Request $request, $id)
    {
        try {
            $owner = JWTAuth::parseToken()->authenticate();

            $request->validate([
                'status' => 'required|in:accept,reject'
            ]);

            $booking = Booking::with('kos')->find($id);

            if (!$booking) {
                return response()->json(['message' => 'Booking tidak ditemukan'], 404);
            }

            $kos = $booking->kos;
            $ownerColumn = collect(['owner_id', 'admin_id', 'user_id'])
                ->first(fn($col) => isset($kos->$col));

            if (!$ownerColumn) {
                return response()->json([
                    'message' => 'Kolom pemilik kos tidak ditemukan di data kos'
                ], 500);
            }

            if ($kos->$ownerColumn !== $owner->id) {
                return response()->json([
                    'message' => 'Tidak diizinkan ubah status booking ini'
                ], 403);
            }

            $booking->update([
                'status' => $request->status,
                'approved_at' => $request->status === 'accept' ? Carbon::now() : null
            ]);

            return response()->json([
                'message' => 'Status booking berhasil diperbarui',
                'data' => $booking
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui status booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Society lihat detail booking
    public function show($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $booking = Booking::with('kos', 'user')
                ->where('user_id', $user->id)
                ->find($id);

            if (!$booking) {
                return response()->json(['message' => 'Booking tidak ditemukan'], 404);
            }

            return response()->json([
                'message' => 'Detail booking',
                'data' => $booking
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil detail booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Society cetak nota booking (PDF)
    public function printNota($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $booking = Booking::with(['kos', 'user'])
                ->where('user_id', $user->id)
                ->find($id);

            if (!$booking) {
                return response()->json(['message' => 'Booking tidak ditemukan'], 404);
            }

            // Generate PDF pakai view Blade
            $pdf = Pdf::loadView('pdf.booking_nota', compact('booking'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('nota-booking-' . $booking->id . '.pdf');
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mencetak nota booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
