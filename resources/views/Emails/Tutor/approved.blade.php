<p>Halo {{ $teacher->user->full_name }},</p>
<p>Selamat! Berkas pendaftaran Anda telah disetujui. Jenjang yang dapat Anda ajar adalah: <strong>{{ implode(', ', $approvedLevels) }}</strong>.</p>
<p>Berikut adalah kredensial akun Anda:</p>
<ul>
    <li>Username: <strong>{{ $username }}</strong></li>
    <li>Password: <strong>{{ $password }}</strong></li>
</ul>
<p><strong>PANDUAN SOP MENGAJAR:</strong></p>
<ol>
    <li>Harap segera masuk dan ubah password sementara Anda.</li>
    <li>Pastikan selalu menekan tombol Clock-In di awal sesi dan Clock-Out di akhir sesi.</li>
    <li>Lengkapi profil materi dan jadwal ketersediaan Anda agar siswa dapat mulai mendaftar.</li>
</ol>
<p>Terima kasih,<br>Tim Kawal Belajar</p>