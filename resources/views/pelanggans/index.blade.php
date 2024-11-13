@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>Daftar Pelanggan</h2>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <a href="{{ route('pelanggans.create') }}" class="btn btn-success mb-3">Tambah Pelanggan</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Nomor HP</th>
                    <th>Nama Mitra</th>
                    <th>File Excel</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pelanggans as $pelanggan)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $pelanggan->nama }}</td>
                        <td>{{ $pelanggan->nomor_hp }}</td>
                        <td>{{ $pelanggan->nama_mitra }}</td>
                        <td>
                            @if ($pelanggan->file_excel)
                                <a href="{{ asset('storage/' . $pelanggan->file_excel) }}" target="_blank">Unduh</a>
                            @else
                                Tidak ada file
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pelanggans.show', $pelanggan->id) }}" class="btn btn-info btn-sm">Lihat</a>
                            <a href="{{ route('pelanggans.edit', $pelanggan->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('pelanggans.destroy', $pelanggan->id) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
