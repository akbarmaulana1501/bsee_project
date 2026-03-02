<?php

namespace App\Http\Controllers;

use App\Models\ETLReport;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function uploadData()
    {
        return view('admin.upload.upload_data');
    }

    public function updateData()
    {
        $data = ETLReport::all();
        return view('admin.update.update_data', compact('data'));
    }

    public function uploadFile(Request $request)
    {
        $uploadedFiles = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $file->storeAs('uploads', $filename, 'public');
                $uploadedFiles[] = ['name' => $filename];
            }

            return response()->json([
                'success' => true,
                'files' => $uploadedFiles
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded.'
        ]);
    }

    public function updateFile(Request $request)
    {
        // Validasi input
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
            'bulan' => 'required|string'
        ]);

        // Ambil nama bulan dan nama file
        $bulan = strtolower($request->input('bulan'));
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // Buat path folder berdasarkan bulan, dan simpan dengan nama asli
        $folderPath = "uploads/$bulan";
        $path = $file->storeAs($folderPath, $filename);

        return response()->json([
            'success' => true,
            'message' => "File berhasil diupload ke folder $bulan.",
            'path' => $path,
        ]);
    }

    public function tambah_data()
    {
        return view('admin.update.tambah_data');
    }

    public function simpan_data(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'periode'       => 'required|string',
            'tonnage_coal'  => 'required|numeric',
            'price_ton'     => 'required|numeric',
            'price_total'   => 'required|numeric',
            'total'         => 'required|numeric',
            'pendapatan'    => 'required|numeric',
        ]);

        // Simpan ke database
        $report = new ETLReport();
        $report->periode       = $validated['periode'];
        $report->tonnage_coal  = $validated['tonnage_coal'];
        $report->price_per_ton_usd     = $validated['price_ton'];
        $report->price_total_usd   = $validated['price_total'];
        $report->total_rp         = $validated['total'];
        $report->pendapatan    = $validated['pendapatan'];
        $report->save();

        // Redirect dengan pesan sukses
        return redirect()->route('update_data')->with('success_simpan', 'Data berhasil disimpan');
    }

    public function editData($id)
    {
        $report = ETLReport::findOrFail($id);
        return view('admin.update.edit_data', compact('report'));
    }

    public function updateDataAction(Request $request, $id)
    {
        $validated = $request->validate([
            'periode'       => 'required|string',
            'tonnage_coal'  => 'required|numeric',
            'price_per_ton_usd' => 'required|numeric',
            'price_total_usd'   => 'required|numeric',
            'total_rp'      => 'required|numeric',
            'pendapatan'    => 'required|numeric',
        ]);

        $report = ETLReport::findOrFail($id);
        $report->update($validated);

        return redirect()->route('update_data')->with('success_update', 'Data berhasil diperbarui');
    }
}
