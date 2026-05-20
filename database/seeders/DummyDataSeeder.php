<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Models\LogAkses;
use App\Models\MonitoringListrik;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $kamar = Kamar::first();
        $penghuni = Penghuni::first();

        if ($kamar && $penghuni) {
            // Dummy Log Akses
            LogAkses::create([
                'uid' => '1A2B3C4D',
                'penghuni_id' => $penghuni->id,
                'kamar_id' => $kamar->id,
                'status' => 'berhasil',
                'aksi' => 'masuk',
                'keterangan' => 'Akses normal'
            ]);
            LogAkses::create([
                'uid' => '1A2B3C4D',
                'penghuni_id' => $penghuni->id,
                'kamar_id' => $kamar->id,
                'status' => 'berhasil',
                'aksi' => 'keluar',
                'keterangan' => 'Akses normal',
                'waktu' => now()->subMinutes(30)
            ]);
            LogAkses::create([
                'uid' => 'UNKNOWN123',
                'status' => 'ditolak',
                'aksi' => 'masuk',
                'keterangan' => 'Kartu tidak terdaftar',
                'waktu' => now()->subHour()
            ]);
        }
        
        // Dummy Monitoring Listrik
        foreach (Kamar::all() as $k) {
            MonitoringListrik::create([
                'kamar_id' => $k->id,
                'kwh' => rand(10, 50) + (rand(0, 999) / 1000),
                'tegangan' => 220 + (rand(-5, 5)),
                'arus' => rand(1, 5) + (rand(0, 99) / 100),
                'daya' => rand(200, 1000)
            ]);
        }
    }
}
