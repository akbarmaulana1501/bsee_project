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
                <h5 class="card-title fw-semibold mb-4">Edit Data</h5>

                <form action="{{ route('update.data.action', $report->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="">Periode</label>
                        <input type="text" name="periode" class="form-control" value="{{ $report->periode }}">
                    </div>
                    <div class="mb-3">
                        <label for="">Tonnage Coal</label>
                        <input type="text" name="tonnage_coal" class="form-control" value="{{ $report->tonnage_coal }}">
                    </div>
                    <div class="mb-3">
                        <label for="">Price/Ton ($)</label>
                        <input type="text" name="price_per_ton_usd" class="form-control" value="{{ $report->price_per_ton_usd }}">
                    </div>
                    <div class="mb-3">
                        <label for="">Price Total ($)</label>
                        <input type="text" name="price_total_usd" class="form-control" value="{{ $report->price_total_usd }}">
                    </div>
                    <div class="mb-3">
                        <label for="">Total (Rp)</label>
                        <input type="text" name="total_rp" class="form-control" value="{{ $report->total_rp }}">
                    </div>
                    <div class="mb-3">
                        <label for="">Pendapatan (Rp)</label>
                        <input type="text" name="pendapatan" class="form-control" value="{{ $report->pendapatan }}">
                    </div>

                    <button type="submit" class="btn btn-success mt-2">Update Data</button>
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