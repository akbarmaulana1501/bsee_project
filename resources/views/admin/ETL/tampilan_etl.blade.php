@extends('layouts.app')

@section('content')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- jQuery (wajib sebelum DataTables) -->

<style>
    #drop-area {
        background-color: #f8f9fa;
        transition: background-color 0.3s;
    }

    #drop-area.bg-light {
        background-color: #e2e6ea !important;
    }
</style>


<!-- Upload Form -->
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data berhasil disimpan ke database!',
        confirmButtonColor: '#3085d6',
    });
</script>
@endif

@if(session('success_password'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Password berhasil diupdate!',
        confirmButtonColor: '#3085d6',
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: 'Gagal meyimpan data!',
        confirmButtonColor: '#d33',
    });
</script>
@endif

<div class="container">
    <div class="col-lg-6 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-semibold mb-4">Update Data</h5>
                <!-- Upload Form -->
                <form action="{{ route('etl.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                    <button type="submit" class="btn btn-primary mt-2">Preview Data</button>
                </form>
            </div>
        </div>
    </div>

    @if(session('preview_data'))
    <hr>
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-semibold mb-4">Tampilan Data</h5>
                <form action="{{ route('etl.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="data" value="{{ base64_encode(serialize(session('preview_data'))) }}">
                    <table id="previewTable" class="table table-bordered display">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Tonnage Coal</th>
                                <th>$ Price/Ton</th>
                                <th>$ Price Total</th>
                                <th>Rp Total</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('preview_data') as $row)
                            <tr>
                                <td>{{ $row['periode'] }}</td>
                                <td>{{ $row['tonnage_coal'] }}</td>
                                <td>{{ $row['price_ton'] }}</td>
                                <td>{{ $row['price_total'] }}</td>
                                <td>{{ $row['rp_total'] }}</td>
                                <td>{{ $row['pendapatan'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success">Simpan ke Database</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#previewTable').DataTable({
            responsive: true,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "→",
                    previous: "←"
                },
                zeroRecords: "Data tidak ditemukan",
            }
        });
    });
</script>
@endsection