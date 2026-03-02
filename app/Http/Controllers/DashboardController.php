<?php

namespace App\Http\Controllers;

use App\Models\AktivitasLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Filter tanggal (optional)
            $start = $request->start_date;
            $end = $request->end_date;

            // Ambil data e_t_l_reports
            $kpi_data = DB::table('e_t_l_reports')->get();

            if ($kpi_data->isEmpty()) {
                throw new \Exception('Data ETL Report masih kosong.');
            }

            $latest_year_suffix = $kpi_data
                ->map(fn($item) => substr($item->periode, -2))
                ->max();

            $filtered_data = $kpi_data->filter(fn($item) => substr($item->periode, -2) === $latest_year_suffix);

            $bulan = $filtered_data->map(fn($item) => explode('-', $item->periode)[0])
                ->unique()->values();

            $lastMonth = explode('-', optional($filtered_data->last())->periode ?? 'January-00')[0];

            $kpi = [
                'pendapatan' => $filtered_data->sum('pendapatan'),
                'produksi' => $filtered_data->sum('tonnage_coal'),
                'standar_produksi' => optional($filtered_data->last())->tonnage_coal ?? 0,
                'cost' => $kpi_data->sum('total_rp'),
                'tahun' => '20' . $latest_year_suffix,
                'bulan' => $lastMonth,
                'jam_kerja' => 0,
            ];

            // Chart Harian
            $grouped = DB::table('e_t_l_reports')
                ->select(
                    DB::raw('DATE(periode) as tanggal'),
                    DB::raw('SUM(tonnage_coal) as produksi'),
                    DB::raw('SUM(total_rp) as bbm')
                )
                ->when($start && $end, fn($query) => $query->whereBetween('periode', [$start, $end]))
                ->groupBy(DB::raw('DATE(periode)'))
                ->orderBy('tanggal')
                ->get();

            $chart_data = [
                'tanggal' => $grouped->pluck('tanggal'),
                'produksi' => $grouped->pluck('produksi'),
                'bbm' => $grouped->pluck('bbm'),
            ];

            // Pie Chart
            $pie_chart = DB::table('aktivitas_logs')
                ->select('aktivitas', DB::raw('COUNT(*) as total'))
                ->groupBy('aktivitas')
                ->pluck('total', 'aktivitas');

            // Table Detail
            $data_table = DB::table('aktivitas_logs')
                ->when($start && $end, fn($query) => $query->whereRaw('DATE(tanggal) BETWEEN ? AND ?', [$start, $end]))
                ->get();

            // Ambil pendapatan terakhir
            $pendapatan_terakhir = DB::table('e_t_l_reports')
                ->select('pendapatan', 'periode')
                ->get()
                ->map(function ($item) {
                    $date = \DateTime::createFromFormat('F-y', $item->periode);
                    return (object) [
                        'tahun' => (int) $date->format('Y'),
                        'bulan' => (int) $date->format('m'),
                        'pendapatan' => $item->pendapatan,
                        'periode' => $item->periode,
                    ];
                })
                ->sortByDesc(fn($item) => $item->tahun * 100 + $item->bulan)
                ->first();

            // Ambil biaya terakhir
            $biaya_terakhir = DB::table('aktivitas_logs')
                ->selectRaw('YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, SUM(biaya) as total_biaya')
                ->groupByRaw('YEAR(tanggal), MONTH(tanggal)')
                ->orderByDesc('tahun')
                ->orderByDesc('bulan')
                ->first();

            // Hitung laba terakhir
            $laba_terakhir = $pendapatan_terakhir->pendapatan - ($biaya_terakhir->total_biaya ?? 0);

            // Fungsi format rupiah bisa dijadikan helper di luar, sementara pakai inline ini:
            $formatted_laba = 'Rp ' . number_format($laba_terakhir, 0, ',', '.');

            // Jika ingin menampilkan laba terakhir di KPI
            $kpi['laba'] = $formatted_laba;

            // *** Bagian ini perlu data produksi per bulan dan modal per ton per bulan *** //
            // Pastikan variabel $produksi_perbulan dan $modal_per_ton_per_bulan didefinisikan sebelumnya.
            // Saya contohkan dummy data agar tidak error:
            $produksi_perbulan = DB::table('e_t_l_reports')
                ->selectRaw("DATE_FORMAT(periode, '%Y-%m') as bulan, SUM(tonnage_coal) as total_produksi")
                ->groupBy(DB::raw("DATE_FORMAT(periode, '%Y-%m')"))
                ->get();

            // Contoh data modal per ton per bulan (dummy, ganti dengan data asli)
            $modal_per_ton_per_bulan = [
                // '2025-07' => 100000, // contoh format key: 'YYYY-MM'
            ];

            $pendapatan_perbulan = [];

            foreach ($produksi_perbulan as $item) {
                $bulan = $item->bulan;
                $total_produksi = $item->total_produksi;

                $total_biaya = DB::table('aktivitas_logs')
                    ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
                    ->sum('biaya');

                $modal_per_ton = $modal_per_ton_per_bulan[$bulan] ?? 0;

                $total_pendapatan = $total_produksi * $modal_per_ton;
                $laba = $total_pendapatan - $total_biaya;

                $pendapatan_perbulan[$bulan] = [
                    'produksi_ton' => $total_produksi,
                    'pendapatan' => $total_pendapatan,
                    'biaya' => $total_biaya,
                    'laba' => $laba,
                ];
            }

            // Hitung total pendapatan dan total biaya setahun
            $total_pendapatan_setahun = DB::table('e_t_l_reports')->sum('pendapatan');
            $total_biaya_setahun = DB::table('aktivitas_logs')->sum('biaya');

            // Hitung total laba setahun
            $total_laba_setahun = $total_pendapatan_setahun - $total_biaya_setahun;

            // Hitung margin
            $margin_persen = $total_pendapatan_setahun > 0
                ? round(($total_laba_setahun / $total_pendapatan_setahun) * 100, 2)
                : 0;

            // Simpan di KPI
            $kpi['margin'] = $margin_persen . '%';

            // Set batas minimum margin
            $margin_minimum = 20; // bisa diubah sesuai kebutuhan
            $kpi['margin_status'] = $margin_persen >= $margin_minimum ? 'OK' : 'Warning';


            return view('admin.dashboard', compact(
                'kpi',
                'chart_data',
                'pie_chart',
                'data_table',
                'pendapatan_perbulan'
            ));
        } catch (\Exception $e) {
            return view('admin.dashboard', [
                'kpi' => [
                    'pendapatan' => 0,
                    'produksi' => 0,
                    'standar_produksi' => 0,
                    'bbm' => 0,
                    'tahun' => '-',
                    'bulan' => '-',
                    'jam_kerja' => 0,
                    'laba' => 'Rp 0',
                ],
                'chart_data' => [
                    'tanggal' => [],
                    'produksi' => [],
                    'bbm' => [],
                ],
                'pie_chart' => [],
                'data_table' => [],
                'pendapatan_perbulan' => [],
                'error_message' => $e->getMessage()
            ]);
        }
    }

    // public function index(Request $request)
    // {
    //     try {
    //         // Filter tanggal (optional)
    //         $start = $request->start_date;
    //         $end = $request->end_date;

    //         // Ambil semua data e_t_l_reports (setahun penuh)
    //         $kpi_data = DB::table('e_t_l_reports')
    //             ->when($start && $end, fn($query) => $query->whereBetween('periode', [$start, $end]))
    //             ->get();

    //         if ($kpi_data->isEmpty()) {
    //             throw new \Exception('Data ETL Report masih kosong.');
    //         }

    //         // Tahun terakhir
    //         $latest_year_suffix = $kpi_data
    //             ->map(fn($item) => substr($item->periode, -2))
    //             ->max();

    //         $filtered_data = $kpi_data->filter(fn($item) => substr($item->periode, -2) === $latest_year_suffix);

    //         $bulan = $filtered_data->map(fn($item) => explode('-', $item->periode)[0])
    //             ->unique()->values();

    //         $lastMonth = explode('-', optional($filtered_data->last())->periode ?? 'January-00')[0];

    //         // KPI utama
    //         $kpi = [
    //             'pendapatan' => $filtered_data->sum('pendapatan'),
    //             'produksi' => $filtered_data->sum('tonnage_coal'),
    //             'standar_produksi' => optional($filtered_data->last())->tonnage_coal ?? 0,
    //             'cost' => $filtered_data->sum('total_rp'),
    //             'tahun' => '20' . $latest_year_suffix,
    //             'bulan' => $lastMonth,
    //             'jam_kerja' => 0,
    //         ];

    //         // ===================== Hitung Laba Tahunan =====================
    //         $total_laba = 0;
    //         $total_pendapatan = 0;

    //         $produksi_perbulan = DB::table('e_t_l_reports')
    //             ->selectRaw("DATE_FORMAT(periode, '%Y-%m') as bulan, SUM(tonnage_coal) as total_produksi, SUM(pendapatan) as total_pendapatan")
    //             ->groupBy(DB::raw("DATE_FORMAT(periode, '%Y-%m')"))
    //             ->get();

    //         $pendapatan_perbulan = [];

    //         foreach ($produksi_perbulan as $item) {
    //             $bulanKey = $item->bulan;

    //             $total_biaya = DB::table('aktivitas_logs')
    //                 ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulanKey])
    //                 ->sum('biaya');

    //             $laba_bulan = $item->total_pendapatan - $total_biaya;

    //             $pendapatan_perbulan[$bulanKey] = [
    //                 'produksi_ton' => $item->total_produksi,
    //                 'pendapatan' => $item->total_pendapatan,
    //                 'biaya' => $total_biaya,
    //                 'laba' => $laba_bulan,
    //             ];

    //             $total_laba += $laba_bulan;
    //             $total_pendapatan += $item->total_pendapatan;
    //         }

    //         // Hitung persentase laba tahunan
    //         $persentase_laba = $total_pendapatan > 0
    //             ? ($total_laba / $total_pendapatan) * 100
    //             : 0;

    //         // Simpan ke KPI
    //         $kpi['laba'] = number_format($persentase_laba, 2) . '%';

    //         // ===================== Chart Harian =====================
    //         $grouped = DB::table('e_t_l_reports')
    //             ->select(
    //                 DB::raw('DATE(periode) as tanggal'),
    //                 DB::raw('SUM(tonnage_coal) as produksi'),
    //                 DB::raw('SUM(total_rp) as bbm')
    //             )
    //             ->when($start && $end, fn($query) => $query->whereBetween('periode', [$start, $end]))
    //             ->groupBy(DB::raw('DATE(periode)'))
    //             ->orderBy('tanggal')
    //             ->get();

    //         $chart_data = [
    //             'tanggal' => $grouped->pluck('tanggal'),
    //             'produksi' => $grouped->pluck('produksi'),
    //             'bbm' => $grouped->pluck('bbm'),
    //         ];

    //         // ===================== Pie Chart =====================
    //         $pie_chart = DB::table('aktivitas_logs')
    //             ->select('aktivitas', DB::raw('COUNT(*) as total'))
    //             ->groupBy('aktivitas')
    //             ->pluck('total', 'aktivitas');

    //         // ===================== Table Detail =====================
    //         $data_table = DB::table('aktivitas_logs')
    //             ->when($start && $end, fn($query) => $query->whereRaw('DATE(tanggal) BETWEEN ? AND ?', [$start, $end]))
    //             ->get();

    //         return view('admin.dashboard', compact(
    //             'kpi',
    //             'chart_data',
    //             'pie_chart',
    //             'data_table',
    //             'pendapatan_perbulan'
    //         ));
    //     } catch (\Exception $e) {
    //         return view('admin.dashboard', [
    //             'kpi' => [
    //                 'pendapatan' => 0,
    //                 'produksi' => 0,
    //                 'standar_produksi' => 0,
    //                 'bbm' => 0,
    //                 'tahun' => '-',
    //                 'bulan' => '-',
    //                 'jam_kerja' => 0,
    //                 'laba' => '0%',
    //             ],
    //             'chart_data' => [
    //                 'tanggal' => [],
    //                 'produksi' => [],
    //                 'bbm' => [],
    //             ],
    //             'pie_chart' => [],
    //             'data_table' => [],
    //             'pendapatan_perbulan' => [],
    //             'error_message' => $e->getMessage()
    //         ]);
    //     }
    // }



    public function get_data_bbm_produksi()
    {
        // Ambil data produksi dari e_t_l_reports
        $data = DB::table('e_t_l_reports')
            ->select(
                DB::raw('periode as tanggal'),
                'tonnage_coal as produksi'
            )
            ->whereNotNull('created_at')
            ->orderByDesc('periode')
            ->limit(7)
            ->get()
            ->sortBy('tanggal') // urutkan dari tanggal paling lama
            ->values();
        // Ambil data bbm dari aktivitas_logs
        $data2 = DB::table('aktivitas_logs')
            ->select(
                DB::raw('DATE(tanggal) as tanggal'),
                'bbm'
            )
            ->whereNotNull('tanggal')
            ->orderByDesc('tanggal')
            ->limit(7)
            ->get()
            ->sortBy('tanggal') // urutkan dari tanggal paling lama
            ->values();

        return response()->json([
            'tanggal' => $data->pluck('tanggal'),
            'produksi' => $data->pluck('produksi'),
            'bbm' => $data2->pluck('bbm'),
        ]);
    }

    public function get_data_aktivitas()
    {
        $data = DB::table('aktivitas_logs')
            ->select('aktivitas', DB::raw('COUNT(*) as total'))
            ->groupBy('aktivitas')
            ->get();

        return response()->json([
            'labels' => $data->pluck('aktivitas'),
            'data' => $data->pluck('total'),
        ]);
    }

    public function pendapatan_vs_biaya()
    {
        DB::statement("SET lc_time_names = 'en_US'");

        $data = DB::table('e_t_l_reports')
            ->selectRaw("
            DATE_FORMAT(STR_TO_DATE(CONCAT('01-', periode), '%d-%M-%y'), '%Y-%m') as bulan,
            SUM(pendapatan) as total_pendapatan
        ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $biaya = DB::table('aktivitas_logs')
            ->selectRaw("
            DATE_FORMAT(tanggal, '%Y-%m') as bulan,
            SUM(biaya) as total_biaya
        ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $labels = [];
        $pendapatan = [];
        $biayaArr = [];

        foreach ($data as $item) {
            if (!empty($biaya[$item->bulan])) { // hanya ambil kalau biaya ada
                $labels[] = $item->bulan;
                $pendapatan[] = $item->total_pendapatan;
                $biayaArr[] = $biaya[$item->bulan]->total_biaya;
            }
        }

        return response()->json([
            'tanggal' => $labels,
            'pendapatan' => $pendapatan,
            'biaya' => $biayaArr
        ]);
    }

    public function laba_per_bulan()
    {
        DB::statement("SET lc_time_names = 'en_US'");

        // Ambil pendapatan
        $pendapatan = DB::table('e_t_l_reports')
            ->selectRaw("
            DATE_FORMAT(STR_TO_DATE(CONCAT('01-', periode), '%d-%M-%y'), '%Y-%m') as bulan,
            SUM(pendapatan) as total_pendapatan
        ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Ambil biaya
        $biaya = DB::table('aktivitas_logs')
            ->selectRaw("
            DATE_FORMAT(tanggal, '%Y-%m') as bulan,
            SUM(biaya) as total_biaya
        ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Siapkan array untuk chart
        $labels = [];
        $labaArr = [];

        foreach ($pendapatan as $item) {
            if (isset($biaya[$item->bulan])) {
                $biaya_bulan = $biaya[$item->bulan]->total_biaya;
                $laba = $item->total_pendapatan - $biaya_bulan;

                $labels[] = $item->bulan;
                $labaArr[] = $laba;
            }
        }

        return response()->json([
            'tanggal' => $labels,
            'laba' => $labaArr
        ]);
    }

    public function getPendapatan($year)
    {
        // Ambil data, format periode jadi YYYY-MM
        $data = DB::table('e_t_l_reports')
            ->selectRaw("
            DATE_FORMAT(STR_TO_DATE(CONCAT('01-', periode), '%d-%M-%y'), '%Y-%m') as bulan,
            SUM(pendapatan) as total_pendapatan
        ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Filter data sesuai tahun
        $filtered = $data->filter(function ($item) use ($year) {
            return substr($item->bulan, 0, 4) == $year; // ambil 4 digit pertama = tahun
        });

        // Siapkan labels dan values
        $labels = $filtered->map(function ($item) {
            return date('F', strtotime($item->bulan . '-01')); // convert YYYY-MM ke nama bulan
        });
        $values = $filtered->pluck('total_pendapatan');

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    public function getPendapatan2($year)
    {
        // Ambil data, format periode jadi YYYY-MM
        $data = DB::table('e_t_l_reports')
            ->selectRaw("
            DATE_FORMAT(STR_TO_DATE(CONCAT('01-', periode), '%d-%M-%y'), '%Y-%m') as bulan,
            SUM(pendapatan) as total_pendapatan
        ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Filter data sesuai tahun
        $filtered = $data->filter(function ($item) use ($year) {
            return substr($item->bulan, 0, 4) == $year; // ambil 4 digit pertama = tahun
        });

        // Siapkan labels dan values
        $labels = $filtered->map(function ($item) {
            return date('F', strtotime($item->bulan . '-01')); // convert YYYY-MM ke nama bulan
        });
        $values = $filtered->pluck('total_pendapatan');

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }


    public function getProduksi($year)
    {
        // Ambil data, format periode jadi YYYY-MM
        $data = DB::table('e_t_l_reports')
            ->selectRaw("
            DATE_FORMAT(STR_TO_DATE(CONCAT('01-', periode), '%d-%M-%y'), '%Y-%m') as bulan,
            SUM(tonnage_coal) as total_produksi
        ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Filter data sesuai tahun
        $filtered = $data->filter(function ($item) use ($year) {
            return substr($item->bulan, 0, 4) == $year; // ambil 4 digit pertama = tahun
        });

        // Siapkan labels dan values
        $labels = $filtered->map(function ($item) {
            return date('F', strtotime($item->bulan . '-01')); // convert YYYY-MM ke nama bulan
        });
        $values = $filtered->pluck('total_produksi');

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    public function getProduksi2($year)
    {
        // Ambil data, format periode jadi YYYY-MM
        $data = DB::table('e_t_l_reports')
            ->selectRaw("
            DATE_FORMAT(STR_TO_DATE(CONCAT('01-', periode), '%d-%M-%y'), '%Y-%m') as bulan,
            SUM(tonnage_coal) as total_produksi
        ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Filter data sesuai tahun
        $filtered = $data->filter(function ($item) use ($year) {
            return substr($item->bulan, 0, 4) == $year; // ambil 4 digit pertama = tahun
        });

        // Siapkan labels dan values
        $labels = $filtered->map(function ($item) {
            return date('F', strtotime($item->bulan . '-01')); // convert YYYY-MM ke nama bulan
        });
        $values = $filtered->pluck('total_produksi');

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }



    // public function getChartData()
    // {
    //     $files = Storage::files('public/uploads');
    //     if (empty($files)) {
    //         return response()->json(['error' => 'No uploaded files found'], 404);
    //     }

    //     // Ambil file terakhir yang di-upload
    //     $latestFile = collect($files)->sortByDesc(fn($f) => Storage::lastModified($f))->first();
    //     $path = storage_path('app/' . $latestFile);

    //     // Load spreadsheet
    //     $spreadsheet = IOFactory::load($path);
    //     $sheet = $spreadsheet->getSheetByName('Sheet2');
    //     $rows = $sheet->toArray();

    //     $data = [];

    //     for ($i = 4; $i < count($rows); $i++) {
    //         $periode = $rows[$i][2];
    //         $tonnage = (float) $rows[$i][3];
    //         $pendapatan = (float) $rows[$i][7];

    //         if ($periode && $tonnage && $pendapatan) {
    //             $data[] = [
    //                 'periode' => date('Y-m', strtotime($periode)),
    //                 'tonnage' => $tonnage,
    //                 'pendapatan' => $pendapatan,
    //             ];
    //         }
    //     }

    //     return response()->json($data);
    // }
}
