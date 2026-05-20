<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanGangguan extends Model
{
    protected $table = 'laporan_gangguan';

    protected $fillable = [
        'no_laporan',
        'nama_penghuni',
        'no_kamar',
        'kategori',
        'deskripsi',
        'urgensi',
        'foto_bukti',
        'status',
        'catatan_admin',
    ];

    /**
     * Generate nomor laporan otomatis: KMR-[no_kamar]-[urutan 3 digit]
     * Menggunakan max sequence (bukan count) agar aman saat ada record yang dihapus.
     */
    public static function generateNoLaporan(string $noKamar): string
    {
        $kamarPad = str_pad($noKamar, 2, '0', STR_PAD_LEFT);
        $prefix   = "KMR-{$kamarPad}-";

        // Ambil semua no_laporan kamar ini, cari angka urutan tertinggi
        $existing = self::where('no_kamar', $noKamar)
            ->where('no_laporan', 'like', $prefix . '%')
            ->pluck('no_laporan');

        $maxNum = 0;
        foreach ($existing as $nol) {
            $num = (int) substr($nol, strlen($prefix));
            if ($num > $maxNum) $maxNum = $num;
        }

        $nextNum   = $maxNum + 1;
        $noLaporan = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        // Safety loop: hindari race condition
        while (self::where('no_laporan', $noLaporan)->exists()) {
            $nextNum++;
            $noLaporan = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        return $noLaporan;
    }

    public function getBadgeColorAttribute(): string
    {
        return match($this->status) {
            'baru'     => 'danger',
            'diproses' => 'warning',
            'selesai'  => 'success',
            default    => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'baru'     => 'Baru',
            'diproses' => 'Diproses',
            'selesai'  => 'Selesai',
            default    => 'Tidak Diketahui',
        };
    }

    public function getUrgensiLabelAttribute(): string
    {
        return match($this->urgensi) {
            'normal'   => 'Normal',
            'mendesak' => 'Mendesak',
            default    => '-',
        };
    }
}
