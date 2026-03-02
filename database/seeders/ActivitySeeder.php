<?php

namespace Database\Seeders;

use App\Models\AktivitasLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 40; $i++) {
            AktivitasLog::create([
                'tanggal' => Carbon::parse('2025-07-01')->addDays($i)->format('Y-m-d'),
                'aktivitas' => ['penambangan', 'pengangkutan', 'pengolahan'][rand(0, 2)],
                'produksi' => rand(1000, 2000),  // ton
                'bbm' => rand(400, 800),        // liter
                'biaya' => rand(20000000, 40000000), // rupiah
            ]);
        }
    }
}
