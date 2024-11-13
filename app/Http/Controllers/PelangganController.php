<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Http\Requests\StorePelangganRequest;
use App\Http\Requests\UpdatePelangganRequest;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pelanggans = Pelanggan::all();
        return view('pelanggans.index', compact('pelanggans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('pelanggans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_hp' => 'required|string|max:15',
            'nama_mitra' => 'required|string|max:255',
            'file_excel' => 'nullable|file|mimes:xlsx,xls|max:2048'
        ]);

        $filePath = null;
        $jsonPath = null;

        if ($request->hasFile('file_excel')) {
            // Format timestamp
            $timestamp = now()->format('YmdHis');

            // Create Excel filename
            $fileName = "{$request->nama}_{$request->nama_mitra}_{$timestamp}." .
                $request->file('file_excel')->getClientOriginalExtension();

            // Store Excel file
            $filePath = $request->file('file_excel')->storeAs('uploads', $fileName, 'public');

            // Create JSON filename
            $jsonFileName = "{$request->nama}_{$request->nama_mitra}_{$timestamp}.json";
            $jsonPath = "json/{$jsonFileName}"; // Store in json subfolder
        }

        // Create pelanggan record
        $pelanggan = Pelanggan::create([
            'nama' => $request->nama,
            'nomor_hp' => $request->nomor_hp,
            'nama_mitra' => $request->nama_mitra,
            'file_excel' => $filePath,
            'url_json' => $jsonPath,
        ]);

        // Convert Excel to JSON if file exists
        if ($filePath) {
            $this->convertExcelToJson(
                storage_path("app/public/$filePath"),
                storage_path("app/public/$jsonPath")
            );
        }

        return redirect()->route('pelanggans.index')
            ->with('success', 'Data pelanggan berhasil ditambahkan');
    }


    protected function convertExcelToJson($excelPath, $jsonPath)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);

        // Initialize structured data array
        $structuredData = [
            "Protein" => [],
            "Karbohidrat" => [],
            "Sayur" => [],
            "Buah" => [],
            "Pelengkap" => []
        ];

        $sheets = ['Protein', 'Karbohidrat', 'Sayur', 'Buah', 'Pelengkap'];

        foreach ($sheets as $sheetName) {
            $worksheet = $spreadsheet->getSheetByName($sheetName);

            if ($worksheet) {
                $dataArray = $worksheet->toArray(null, true, true, true);

                foreach ($dataArray as $rowIndex => $row) {
                    if ($rowIndex === 0) continue; // Skip header row

                    $nama = $row['A'];
                    $harga = $row['B'];

                    if ($nama && $harga) {
                        $structuredData[$sheetName][$nama] = (int) $harga;
                    }
                }
            }
        }

        // Ensure json directory exists
        $jsonDir = dirname($jsonPath);
        if (!file_exists($jsonDir)) {
            mkdir($jsonDir, 0755, true);
        }

        // Save JSON file
        file_put_contents($jsonPath, json_encode(
            $structuredData,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ));

        return $jsonPath;
    }




    /**
     * Display the specified resource.
     */
    public function show(Pelanggan $pelanggan)
    {
        $fileData = [];

        if ($pelanggan->file_excel) {
            $filePath = storage_path("app/public/{$pelanggan->file_excel}");
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheets = ['Protein', 'Karbohidrat', 'Sayur', 'Buah', 'Pelengkap'];

            foreach ($sheets as $sheetName) {
                $worksheet = $spreadsheet->getSheetByName($sheetName);
                if ($worksheet) {
                    $data = $worksheet->toArray(null, true, true, true);
                    foreach ($data as $index => $row) {
                        if ($index === 0) continue; // Skip header
                        if (!empty($row['A'])) { // Only add if name exists
                            $fileData[$sheetName][] = [
                                'nama' => $row['A'] ?? '',
                                'harga' => $row['B'] ?? 0,
                            ];
                        }
                    }
                }
            }
        }

        return view('pelanggans.show', compact('pelanggan', 'fileData'));
    }

    public function updateExcel(Request $request, Pelanggan $pelanggan)
    {
        $filePath = storage_path("app/public/{$pelanggan->file_excel}");
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

        foreach ($request->input('data') as $category => $items) {
            $worksheet = $spreadsheet->getSheetByName($category);

            if ($worksheet) {
                // Clear existing data
                $highestRow = $worksheet->getHighestRow();
                if ($highestRow > 1) {
                    $worksheet->removeRow(1, $highestRow - 1);
                }

                // Add new data
                foreach ($items as $index => $item) {
                    $rowIndex = $index + 1; // Start from row 2
                    $worksheet->setCellValue("A{$rowIndex}", $item['nama']);
                    $worksheet->setCellValue("B{$rowIndex}", $item['harga']);
                }
            }
        }

        // Save Excel changes
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        // Update JSON file
        if ($pelanggan->url_json) {
            $this->convertExcelToJson(
                $filePath,
                storage_path("app/public/{$pelanggan->url_json}")
            );
        }

        return redirect()->route('pelanggans.show', $pelanggan->id)
            ->with('success', 'Data Excel berhasil diperbarui');
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggans.edit', compact('pelanggan'));
    }
    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */


    public function update(Request $request, Pelanggan $pelanggan)
    {
        try {
            Log::info('Update request received', [
                'has_file' => $request->hasFile('file_excel'),
                'all_data' => $request->all()
            ]);

            // Excel file validation
            if ($request->hasFile('file_excel')) {
                $file = $request->file('file_excel');
                $extension = strtolower($file->getClientOriginalExtension());

                $validExcelMimes = [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'application/octet-stream',
                    'application/zip'
                ];

                if (
                    !in_array($file->getMimeType(), $validExcelMimes) &&
                    !in_array($extension, ['xlsx', 'xls'])
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File harus berformat Excel (xlsx atau xls)'
                    ], 422);
                }

                if ($file->getSize() > 2048 * 1024) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ukuran file tidak boleh lebih dari 2MB'
                    ], 422);
                }
            }

            // Other fields validation
            $validationRules = [];
            if ($request->has('nama')) $validationRules['nama'] = 'required|string|max:255';
            if ($request->has('nomor_hp')) $validationRules['nomor_hp'] = 'required|string|max:15';
            if ($request->has('nama_mitra')) $validationRules['nama_mitra'] = 'required|string|max:255';

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first()
                ], 422);
            }

            $updateData = [];

            // Handle Excel file update
            if ($request->hasFile('file_excel')) {
                $file = $request->file('file_excel');

                Log::info('File information', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize()
                ]);

                // Delete old Excel file
                if ($pelanggan->file_excel && Storage::disk('public')->exists($pelanggan->file_excel)) {
                    Storage::disk('public')->delete($pelanggan->file_excel);
                }

                // Delete old JSON file
                if ($pelanggan->url_json && Storage::disk('public')->exists($pelanggan->url_json)) {
                    Storage::disk('public')->delete($pelanggan->url_json);
                }

                // Clean and prepare filenames
                $cleanNama = Str::slug($pelanggan->nama);
                $cleanMitra = Str::slug($pelanggan->nama_mitra);
                $timestamp = now()->format('Y-m-d');
                $uniqueId = substr(uniqid(), -4);

                // Create Excel filename
                $excelFilename = sprintf(
                    '%s_%s_%s_%s.%s',
                    $cleanNama,
                    $cleanMitra,
                    $timestamp,
                    $uniqueId,
                    $extension
                );

                // Create JSON filename
                $jsonFilename = sprintf(
                    '%s_%s_%s_%s.json',
                    $cleanNama,
                    $cleanMitra,
                    $timestamp,
                    $uniqueId
                );

                // Define folder structure
                $yearMonth = now()->format('Y/m');
                $excelFolderPath = "excel_files/{$yearMonth}";
                $jsonFolderPath = "json/{$yearMonth}";

                // Store Excel file
                $excelPath = $file->storeAs($excelFolderPath, $excelFilename, 'public');

                // Prepare JSON path
                $jsonPath = "{$jsonFolderPath}/{$jsonFilename}";

                // Update file paths in data
                $updateData['file_excel'] = $excelPath;
                $updateData['url_json'] = $jsonPath;

                // Create JSON directory if it doesn't exist
                $fullJsonPath = storage_path("app/public/{$jsonPath}");
                if (!file_exists(dirname($fullJsonPath))) {
                    mkdir(dirname($fullJsonPath), 0755, true);
                }

                // Convert Excel to JSON
                $this->convertExcelToJson(
                    storage_path("app/public/{$excelPath}"),
                    $fullJsonPath
                );
            }

            // Add other fields if they exist
            if ($request->has('nama')) $updateData['nama'] = $request->nama;
            if ($request->has('nomor_hp')) $updateData['nomor_hp'] = $request->nomor_hp;
            if ($request->has('nama_mitra')) $updateData['nama_mitra'] = $request->nama_mitra;

            // Update the Pelanggan record
            if (!empty($updateData)) {
                $pelanggan->update($updateData);
            }

            return redirect()->route('pelanggans.show', $pelanggan->id)
                ->with('success', 'Data pelanggan berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating pelanggan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }





    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pelanggan $pelanggan)
    {
        // Hapus file Excel jika ada
        if ($pelanggan->file_excel) {
            Storage::disk('public')->delete($pelanggan->file_excel);
        }

        // Hapus data pelanggan
        $pelanggan->delete();

        return redirect()->route('pelanggans.index')->with('success', 'Data pelanggan berhasil dihapus');
    }
}
