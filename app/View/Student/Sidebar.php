<?php

namespace App\View\Components\Student;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Order; // Sesuaikan dengan model jadwal/booking kamu

class Sidebar extends Component
{
    public int $upcomingSessionsCount;

    public function __construct()
    {
        // Ambil hitungan sesi aktif dari database jika user login, atau gunakan nilai default
        if (Auth::check() && Auth::user()->student) {
            // Contoh query asli:
            $this->upcomingSessionsCount = Auth::user()->student->bookings()->where('status', 'confirmed')->count();
            $this->upcomingSessionsCount = 2; 
        } else {
            $this->upcomingSessionsCount = 2; // Dummy untuk testing lokal
        }
    }

    public function render()
    {
        return view('components.student.sidebar');
    }
}