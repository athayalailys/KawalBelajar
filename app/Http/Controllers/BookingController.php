<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    public function checkout(Request $request): RedirectResponse
    {
        // 1. Validasi input menggunakan tabel dan kolom ERD terbaru
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,teacher_id',
            'package_id' => 'required|exists:module_packages,package_id',
            'total_amount' => 'required|numeric|min:0',
        ]);

        // 2. Ambil student_id milik user yang sedang login
        $student = Auth::user()?->student;

        if (!$student) {
            return redirect()->back()->withErrors([
                'error' => 'Profil siswa tidak ditemukan. Silakan selesaikan pendaftaran akun siswa Anda.'
            ]);
        }

        try {
            // 3. Gunakan DB Transaction untuk memastikan integritas data Order & Payment
            $order = DB::transaction(function () use ($validated, $student) {
                
                // Simpan ke tabel orders
                $newOrder = Order::create([
                    'student_id' => $student->student_id,
                    'package_id' => $validated['package_id'],
                    'teacher_id' => $validated['teacher_id'],
                    'order_date' => now(),
                    'total_amount' => $validated['total_amount'],
                    'order_status' => 'pending',
                ]);

                // Inisialisasi record pembayaran ke tabel payments
                Payment::create([
                    'order_id' => $newOrder->order_id,
                    'amount' => $validated['total_amount'],
                    'payment_status' => 'pending',
                    'payment_method' => 'Xendit', // atau diisi saat webhook callback Xendit
                ]);

                return $newOrder;
            });

            // TODO: Panggil API Service Xendit di sini untuk membuat Invoice URL
            // $xenditUrl = $this->xenditService->createInvoice($order);

            return redirect()->back()->with('success', 'Pesanan berhasil dibuat! Mengalihkan ke pembayaran...');

        } catch (\Throwable $e) {
            return redirect()->back()->withErrors([
                'error' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ]);
        }
    }
}