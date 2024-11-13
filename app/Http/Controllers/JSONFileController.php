<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JSONFileController extends Controller
{
    // Mendapatkan file JSON
    public function getJson(Pelanggan $pelanggan)
    {
        // Ambil nama file JSON dari atribut file_excel
        $jsonFileName = pathinfo($pelanggan->file_excel, PATHINFO_FILENAME) . '.json';

        // Periksa apakah file JSON ada
        if (!Storage::exists("public/{$jsonFileName}")) {
            return response()->json(['error' => 'File JSON tidak ditemukan.'], 404);
        }

        // Ambil dan kembalikan konten file JSON
        $jsonData = Storage::get("public/{$jsonFileName}");
        return response()->json(json_decode($jsonData, true));
    }

    // Memperbarui file JSON
    public function updateJson(Request $request, Pelanggan $pelanggan)
    {
        // Validasi data yang diterima
        $request->validate([
            'data' => 'required|array',
        ]);

        // Ambil nama file JSON dari atribut file_excel
        $jsonFileName = pathinfo($pelanggan->file_excel, PATHINFO_FILENAME) . '.json';

        // Simpan data JSON ke file
        Storage::put("public/{$jsonFileName}", json_encode($request->input('data'), JSON_PRETTY_PRINT));

        return response()->json(['success' => 'File JSON berhasil diperbarui.']);
    }
}
