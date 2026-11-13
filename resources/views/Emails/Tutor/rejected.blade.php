<p>Halo {{ $teacher->user->full_name }},</p>
<p>Terima kasih telah mendaftar di Kawal Belajar. Setelah meninjau dokumen yang Anda unggah, dengan berat hati kami menginformasikan bahwa pendaftaran Anda <strong>belum dapat disetujui</strong>.</p>
<p><strong>Alasan Penolakan:</strong></p>
<blockquote style="border-left: 4px solid #f87171; padding-left: 10px; color: #555;">
    {{ $reason }}
</blockquote>
<p><strong>Instruksi Perbaikan:</strong><br>
Silakan login kembali ke akun Anda dan unggah ulang berkas sesuai dengan catatan di atas.</p>
<p>Terima kasih,<br>Tim Kawal Belajar</p>