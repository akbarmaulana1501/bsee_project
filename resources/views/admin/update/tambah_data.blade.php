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
                <h5 class="card-title fw-semibold mb-4">Tambah Data</h5>
                <!-- Upload Form -->
                <form action="{{ route('simpan.data') }}" method="POST">
                    @csrf
                    <!-- <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required> -->
                    <div class="mb-3">
                        <label for="">Periode</label>
                        <input type="text" id="periode" name="periode" class="form-control">
                    </div>
                    <div>
                        <label for="">Tonnage Coal</label>
                        <input type="number" id="tonnage_coal" name="tonnage_coal" class="form-control">
                    </div>
                    <div>
                        <label for="">Price/ton</label>
                        <input type="number" id="price_ton" name="price_ton" class="form-control">
                    </div>
                    <div>
                        <label for="">Price Total</label>
                        <input type="number" id="price_total" name="price_total" class="form-control">
                    </div>
                    <div>
                        <label for="">Total</label>
                        <input type="number" id="total" name="total" class="form-control">
                    </div>
                    <div>
                        <label for="">Pendapatan</label>
                        <input type="number" id="pendapatan" name="pendapatan" class="form-control">
                    </div>
                    <!-- <div>
                        <label for="">Periode</label>
                        <input type="text" id="pendapatan" name="pendapatan" class="form-control">
                    </div> -->
                    <button type="submit" class="btn btn-primary mt-2">Simpan Data</button>
                </form>
            </div>
        </div>
    </div>
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