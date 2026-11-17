<p>Halo {{ $tutorName }},</p>

<p>Selamat! Pendaftaran Anda sebagai tutor KawalBelajar telah disetujui.</p>

<p>Jenjang mengajar yang disetujui Admin:</p>
<ul>
    @foreach ($verifiedLevels as $level)
        <li>{{ $level }}</li>
    @endforeach
</ul>

<p>Gunakan kredensial berikut untuk masuk:</p>
<ul>
    <li>Email: <strong>{{ $email }}</strong></li>
    <li>Kata sandi sementara: <strong>{{ $temporaryPassword }}</strong></li>
</ul>

<p><a href="{{ $loginUrl }}">Masuk ke KawalBelajar</a></p>

<p><strong>Panduan singkat:</strong></p>
<ol>
    <li>Masuk menggunakan email dan kata sandi sementara di atas.</li>
    <li>Segera ganti kata sandi untuk menjaga keamanan akun.</li>
    <li>Lengkapi program, materi, dan ketersediaan jadwal mengajar.</li>
    <li>Lakukan Clock-In saat sesi dimulai dan Clock-Out setelah sesi selesai.</li>
</ol>

<p>Salam,<br>Tim KawalBelajar</p>
