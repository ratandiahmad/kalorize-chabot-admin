<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PelangganJsonController extends Controller
{
    /**
     * Get JSON data for a specific pelanggan
     *
     * @param int $id
     * @return JsonResponse
     */

    public function getJsonDataByPhone($nomor_hp): JsonResponse
    {
        try {
            // Cari pelanggan berdasarkan nomor_hp
            $pelanggan = Pelanggan::where('nomor_hp', $nomor_hp)->firstOrFail();

            // Tentukan path ke data-default.json jika url_json kosong
            $jsonPath = $pelanggan->url_json ?: 'json/data-default.json';

            // Periksa apakah file JSON ada
            if (!Storage::disk('public')->exists($jsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File JSON tidak ditemukan'
                ], 404);
            }

            // Baca dan decode file JSON
            $jsonContent = Storage::disk('public')->get($jsonPath);
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'error' => json_last_error_msg(),
                    'pelanggan_nomor_hp' => $nomor_hp,
                    'file_path' => $jsonPath
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error membaca file JSON'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pelanggan' => [
                        'id' => $pelanggan->id,
                        'nama' => $pelanggan->nama,
                        'nama_mitra' => $pelanggan->nama_mitra,
                    ],
                    'menu_data' => $data
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving JSON data', [
                'error' => $e->getMessage(),
                'pelanggan_nomor_hp' => $nomor_hp
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data'
            ], 500);
        }
    }


    public function getJsonData($id): JsonResponse
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);

            if (!$pelanggan->url_json) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data JSON untuk pelanggan ini'
                ], 404);
            }

            // Check if JSON file exists
            if (!Storage::disk('public')->exists($pelanggan->url_json)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File JSON tidak ditemukan'
                ], 404);
            }

            // Read and decode JSON file
            $jsonContent = Storage::disk('public')->get($pelanggan->url_json);
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'error' => json_last_error_msg(),
                    'pelanggan_id' => $id,
                    'file_path' => $pelanggan->url_json
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error membaca file JSON'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pelanggan' => [
                        'id' => $pelanggan->id,
                        'nama' => $pelanggan->nama,
                        'nama_mitra' => $pelanggan->nama_mitra,
                    ],
                    'menu_data' => $data
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving JSON data', [
                'error' => $e->getMessage(),
                'pelanggan_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data'
            ], 500);
        }
    }

    /**
     * Get all menu categories for a specific pelanggan
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getCategories($id): JsonResponse
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);

            if (!$pelanggan->url_json || !Storage::disk('public')->exists($pelanggan->url_json)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kategori tidak tersedia'
                ], 404);
            }

            $jsonContent = Storage::disk('public')->get($pelanggan->url_json);
            $data = json_decode($jsonContent, true);

            return response()->json([
                'success' => true,
                'categories' => array_keys($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving categories', [
                'error' => $e->getMessage(),
                'pelanggan_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil kategori'
            ], 500);
        }
    }

    /**
     * Get menu items for a specific category
     *
     * @param int $id
     * @param string $category
     * @return JsonResponse
     */
    public function getCategoryItems($id, $category): JsonResponse
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);

            if (!$pelanggan->url_json || !Storage::disk('public')->exists($pelanggan->url_json)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data menu tidak tersedia'
                ], 404);
            }

            $jsonContent = Storage::disk('public')->get($pelanggan->url_json);
            $data = json_decode($jsonContent, true);

            if (!isset($data[$category])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'category' => $category,
                'items' => $data[$category]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving category items', [
                'error' => $e->getMessage(),
                'pelanggan_id' => $id,
                'category' => $category
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data menu'
            ], 500);
        }
    }
}
