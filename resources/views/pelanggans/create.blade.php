@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>Tambah Pelanggan</h2>
        <form action="{{ route('pelanggans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mb-3">
                <label for="nama">Nama</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="nomor_hp">Nomor HP</label>
                <input type="text" name="nomor_hp" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="nama_mitra">Nama Mitra</label>
                <input type="text" name="nama_mitra" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="file_excel">File Excel</label>
                <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
@endsection
