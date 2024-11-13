@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Isi File Excel untuk {{ $pelanggan->nama }}</h1>

        <form action="{{ route('pelanggans.updateExcel', $pelanggan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div id="file-data-container">
                @foreach ($fileData as $category => $items)
                    <div class="category-section" data-category="{{ $category }}">
                        <h3>{{ $category }}</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="text" name="data[{{ $category }}][{{ $index }}][nama]"
                                                value="{{ $item['nama'] }}" class="form-control">
                                        </td>
                                        <td>
                                            <input type="number"
                                                name="data[{{ $category }}][{{ $index }}][harga]"
                                                value="{{ $item['harga'] }}" class="form-control">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger remove-row">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success add-row" data-category="{{ $category }}">Tambah
                            Baris</button>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Event handler untuk tombol "Tambah Baris"
            document.querySelectorAll('.add-row').forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.getAttribute('data-category');
                    const section = this.closest('.category-section');
                    const tbody = section.querySelector('tbody');
                    const rowCount = tbody.children.length;

                    // Buat baris baru
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>
                            <input type="text" name="data[${category}][${rowCount}][nama]" class="form-control">
                        </td>
                        <td>
                            <input type="number" name="data[${category}][${rowCount}][harga]" class="form-control">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-row">Hapus</button>
                        </td>
                    `;

                    tbody.appendChild(newRow);
                });
            });

            // Delegasi event untuk tombol "Hapus"
            document.querySelectorAll('.category-section tbody').forEach(tbody => {
                tbody.addEventListener('click', function(event) {
                    if (event.target.classList.contains('remove-row')) {
                        const row = event.target.closest('tr');
                        row.remove();
                    }
                });
            });
        });
    </script>
@endsection
