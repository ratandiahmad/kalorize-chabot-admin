@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>Edit Pelanggan: {{ $pelanggan->nama }}</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('pelanggans.update', $pelanggan->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" name="nama" id="nama" class="form-control"
                    value="{{ old('nama', $pelanggan->nama) }}" required>
            </div>

            <div class="mb-3">
                <label for="nomor_hp" class="form-label">Nomor HP</label>
                <input type="text" name="nomor_hp" id="nomor_hp" class="form-control"
                    value="{{ old('nomor_hp', $pelanggan->nomor_hp) }}" required>
            </div>

            <div class="mb-3">
                <label for="nama_mitra" class="form-label">Nama Mitra</label>
                <input type="text" name="nama_mitra" id="nama_mitra" class="form-control"
                    value="{{ old('nama_mitra', $pelanggan->nama_mitra) }}" required>
            </div>

            <div class="mb-3">
                <label for="file_excel" class="form-label">File Excel</label>
                <input type="file" name="file_excel" id="file_excel" class="form-control" accept=".xlsx, .xls">
                @if ($pelanggan->file_excel)
                    <small class="form-text text-muted">File saat ini: <a
                            href="{{ asset('storage/' . $pelanggan->file_excel) }}" target="_blank">Unduh</a></small>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Perbarui</button>
            <a href="{{ route('pelanggans.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection
