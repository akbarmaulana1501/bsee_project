<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ETLReport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Session;

class EtlController extends Controller
{
    public function index()
    {
        return view('admin.ETL.tampilan_etl');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $path = $request->file('file')->getRealPath();

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // A, B, C...

        $data = [];
        for ($i = 6; $i <= count($rows); $i++) {
            $row = $rows[$i];

            // Cek apakah ada kolom penting yang kosong
            if (
                empty($row['C']) || // periode
                empty($row['D']) || // tonnage_coal
                empty($row['E']) || // price_ton
                empty($row['F']) || // price_total
                empty($row['G']) || // rp_total
                empty($row['H'])    // pendapatan
            ) {
                return redirect()->route('upload_data')->withErrors([
                    'file' => "Data tidak lengkap di baris ke-$i. Pastikan semua kolom diisi.",
                ]);
            }

            $data[] = [
                'periode'       => $row['C'],
                'tonnage_coal'  => $row['D'],
                'price_ton'     => $row['E'],
                'price_total'   => $row['F'],
                'rp_total'      => $row['G'],
                'pendapatan'    => $row['H'],
            ];
        }

        return redirect()->route('upload_data')->with('preview_data', $data);
    }

    public function preview2(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $path = $request->file('file')->getRealPath();

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // A, B, C...

        $data = [];
        for ($i = 6; $i <= count($rows); $i++) {
            $row = $rows[$i];

            // Cek apakah ada kolom penting yang kosong
            if (
                empty($row['C']) || // periode
                empty($row['D']) || // tonnage_coal
                empty($row['E']) || // price_ton
                empty($row['F']) || // price_total
                empty($row['G']) || // rp_total
                empty($row['H'])    // pendapatan
            ) {
                return redirect()->route('update_data')->withErrors([
                    'file' => "Data tidak lengkap di baris ke-$i. Pastikan semua kolom diisi.",
                ]);
            }

            $data[] = [
                'periode'       => $row['C'],
                'tonnage_coal'  => $row['D'],
                'price_per_ton_usd' => $row['E'],
                'price_total_usd'   => $row['F'],
                'total_rp'      => $row['G'],
                'pendapatan'    => $row['H'],
            ];
        }

        return redirect()->route('update_data')->with('preview_data', $data);
    }

    public function save(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'data' => 'required',
            ]);

            // Decode dan unserialize data
            $raw = unserialize(base64_decode($request->input('data')));

            // Validasi isi data array
            if (!is_array($raw) || empty($raw)) {
                return redirect()->route('upload_data')->with('error', 'Data tidak valid atau kosong.');
            }

            $duplicate_periodes = [];
            $success_count = 0;

            foreach ($raw as $row) {
                if (empty($row['periode'])) {
                    continue;
                }

                $periode = trim($row['periode']);

                // Cek apakah data periode sudah ada
                if (ETLReport::where('periode', $periode)->exists()) {
                    $duplicate_periodes[] = $periode;
                    continue;
                }

                // Simpan data
                ETLReport::create([
                    'periode'           => $periode,
                    'tonnage_coal'      => floatval(str_replace(',', '', $row['tonnage_coal'])),
                    'price_per_ton_usd' => floatval(str_replace(',', '', $row['price_ton'])),
                    'price_total_usd'   => floatval(str_replace(',', '', $row['price_total'])),
                    'total_rp'          => $this->parseRupiah($row['rp_total']),
                    'pendapatan'        => $this->parseRupiah($row['pendapatan']),
                ]);

                $success_count++;
            }

            // Jika ada data yang berhasil disimpan
            if ($success_count > 0) {
                $message = 'Data berhasil disimpan ke database!';

                // Tambahkan info tambahan jika ada yang duplikat
                if (!empty($duplicate_periodes)) {
                    $message .= ' Beberapa data diabaikan karena periode sudah ada: ' . implode(', ', $duplicate_periodes);
                }

                return redirect()->route('upload_data')->with('success', $message);
            }

            // Jika semua data duplikat
            return redirect()->route('upload_data')->with('error', 'Semua data yang diunggah sudah ada dalam database.');
        } catch (\Exception $e) {
            return redirect()->route('upload_data')->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }


    public function save2(Request $request)
    {
        try {
            $request->validate([
                'data' => 'required',
            ]);

            $raw = unserialize(base64_decode($request->input('data')));

            if (!is_array($raw) || empty($raw)) {
                return redirect()->route('update_data')->with('error', 'Data tidak valid atau kosong.');
            }

            $savedCount = 0;
            $duplicateCount = 0;
            $totalCount = count($raw);

            foreach ($raw as $row) {
                if (empty($row['periode'])) {
                    continue;
                }

                $periode = trim($row['periode']);

                if (ETLReport::where('periode', $periode)->exists()) {
                    $duplicateCount++;
                    continue;
                }

                ETLReport::create([
                    'periode'           => $row['periode'],
                    'tonnage_coal'      => floatval(str_replace(',', '', $row['tonnage_coal'])),
                    'price_per_ton_usd' => floatval(str_replace(',', '', $row['price_per_ton_usd'])),
                    'price_total_usd'   => floatval(str_replace(',', '', $row['price_total_usd'])),
                    'total_rp'          => $this->parseRupiah($row['total_rp']),
                    'pendapatan'        => $this->parseRupiah($row['pendapatan']),
                ]);

                $savedCount++;
            }

            if ($savedCount === 0) {
                return redirect()->route('update_data')->with('error', 'Semua data sudah ada (duplikat), tidak ada data yang disimpan.');
            }

            if ($savedCount < $totalCount) {
                return redirect()->route('update_data')->with('success', "$savedCount data berhasil disimpan. $duplicateCount data duplikat dilewati.");
            }

            return redirect()->route('update_data')->with('success', "$savedCount data baru berhasil disimpan ke database!");
        } catch (\Exception $e) {
            return redirect()->route('update_data')->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    private function parseRupiah($value)
    {
        $cleaned = str_replace(['Rp', 'rp', '.', ' ', ','], '', $value);
        return is_numeric($cleaned) ? floatval($cleaned) : 0;
    }
}
