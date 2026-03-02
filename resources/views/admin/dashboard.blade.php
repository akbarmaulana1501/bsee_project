@extends('layouts.app')

@section('content')

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

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="container-fluid">
    <!-- Filter Rentang Tanggal -->
    <form method="GET" action="{{ route('dashboard') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Dari Bulan</label>
                <input type="month" name="start_month" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Sampai Bulan</label>
                <input type="month" name="end_month" class="form-control">
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <button type="reset" class="btn btn-danger">Reset</button>
            </div>
        </div>
    </form>

    <!-- KPI Boxes -->
    <div class="row text-white">
        <div class="col-md-3">
            <div class="bg-primary p-3 rounded">
                <h5>Pendapatan/Tahun <?= $kpi['tahun']; ?> </h5>
                <h3>Rp.{{ number_format($kpi['pendapatan'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-success p-3 rounded">
                <h5>Produksi</h5>
                <h3>{{ number_format($kpi['produksi'], 0, ',', '.') }} Ton</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-warning p-3 rounded text-dark">
                <h5>Total Cost/Tahun <?= $kpi['tahun']; ?></h5>
                <h3>Rp.{{ number_format($kpi['cost'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-danger p-3 rounded">
                <h5>Keuntungan/Bulan</h5>
                <h3>{{ $kpi['laba'] }}</h3>
                <h6> Margin Laba : {{ $kpi['margin'] }}</h6>
                <small>Status: {{ $kpi['margin_status'] }}</small>
            </div>
        </div>
        <!-- <div class="col-md-3">
            <div class="bg-info p-3 rounded">
                <h5>Margin Laba</h5>
                <h3>{{ $kpi['margin'] }}</h3>
                <small>Status: {{ $kpi['margin_status'] }}</small>
            </div>
        </div> -->
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
        </symbol>
        <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
        </symbol>
        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
        </symbol>
    </svg>

    <!-- @if($kpi['standar_produksi'] < 120000)
        <div class="mt-3">
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">
                <use xlink:href="#exclamation-triangle-fill" />
            </svg>
            <div>
                Target untuk Bulan {{ $kpi['bulan'] }} belum tercapai!
            </div>
        </div>
</div>
@endif -->


    <!-- Grafik Tren -->
    <div class="row">
        <!-- Card Chart Tren Produksi vs BBM -->
        <div class="col-md-8 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Tren Produksi</h5>
                        <div class="form-group mb-0 d-flex align-items-center">
                            <label for="chartTypeSelect" class="mr-2 mb-0">Jenis Chart:</label>
                            <select id="chartTypeSelect" class="form-control form-control-sm w-auto">
                                <option value="bar">Bar Chart</option>
                                <option value="line">Line Chart</option>
                            </select>
                        </div>
                    </div>
                    <canvas id="chart_tren" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-8 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Tren Pendapatan vs Cost</h5>
                        <div class="form-group mb-0 d-flex align-items-center">
                            <!-- <label for="chartTypeSelect" class="mr-2 mb-0">Jenis Chart:</label>
                            <select id="chartTypeSelect" class="form-control form-control-sm w-auto">
                                <option value="bar">Bar Chart</option>
                                <option value="line">Line Chart</option>
                            </select> -->
                        </div>
                    </div>
                    <canvas id="chart_tren2" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-8 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Tren Laba</h5>
                        <div class="form-group mb-0 d-flex align-items-center">
                            <!-- <label for="chartTypeSelect" class="mr-2 mb-0">Jenis Chart:</label>
                            <select id="chartTypeSelect" class="form-control form-control-sm w-auto">
                                <option value="bar">Bar Chart</option>
                                <option value="line">Line Chart</option>
                            </select> -->
                        </div>
                    </div>
                    <canvas id="chart_tren3" height="150"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Pendapatan </h5>
                        <div class="form-group mb-0 d-flex align-items-center">
                            <label for="chartTypeSelect4" class="mr-2 mb-0">Tahun:</label>
                            <select id="chartTypeSelect4" class="form-control form-control-sm w-auto">
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>
                    <canvas id="chart_tren4" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-8 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Pendapatan </h5>
                        <div class="form-group mb-0 d-flex align-items-center">
                            <label for="chartTypeSelect5" class="mr-2 mb-0">Tahun:</label>
                            <select id="chartTypeSelect5" class="form-control form-control-sm w-auto">
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>
                    <canvas id="chart_tren5" height="150"></canvas>
                </div>
            </div>
        </div>


        <div class="col-md-8 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Produksi </h5>
                        <div class="form-group mb-0 d-flex align-items-center">
                            <label for="chartTypeSelect6" class="mr-2 mb-0">Tahun:</label>
                            <select id="chartTypeSelect6" class="form-control form-control-sm w-auto">
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>
                    <canvas id="chart_tren6" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-8 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Produksi </h5>
                        <div class="form-group mb-0 d-flex align-items-center">
                            <label for="chartTypeSelect7" class="mr-2 mb-0">Tahun:</label>
                            <select id="chartTypeSelect7" class="form-control form-control-sm w-auto">
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>
                    <canvas id="chart_tren7" height="150"></canvas>
                </div>
            </div>
        </div>



        <!-- Card Pie Chart Aktivitas -->
        <!-- <div class="col-md-4 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <h5 class="text-center mb-4">Distribusi Aktivitas</h5>
                <div class="d-flex justify-content-center">
                    <canvas id="pie_chart" style="max-width: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div> -->
    </div>




    <!-- Table Detail -->
    <!-- <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-semibold mb-4">Data BBM & Produksi</h5>
                <table id="detailTable" class="table table-bordered display">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Aktivitas</th>
                            <th>Produksi (Ton)</th>
                            <th>BBM (Liter)</th>
                            <th>Biaya (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data_table as $row)
                        <tr>
                            <td>{{ $row->tanggal }}</td>
                            <td>{{ $row->aktivitas }}</td>
                            <td>{{ $row->produksi }}</td>
                            <td>{{ $row->bbm }}</td>
                            <td>{{ number_format($row->biaya, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div> -->
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.1.0"></script>

<script>
    const ctx = document.getElementById('chart_tren').getContext('2d');
    let chartInstance; // Simpan chart di variabel global

    // Fungsi untuk membuat chart berdasarkan tipe
    function renderChart(type) {
        $.ajax({
            url: "{{ route('chart_data') }}",
            method: 'GET',
            success: function(response) {
                // Hapus chart lama jika ada
                if (chartInstance) {
                    chartInstance.destroy();
                }

                chartInstance = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: response.tanggal,
                        datasets: [{
                            label: 'Produksi',
                            data: response.produksi,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        // plugins: {
                        //     annotation: {
                        //         annotations: {
                        //             targetLine: {
                        //                 type: 'line',
                        //                 yMin: 120000,
                        //                 yMax: 120000,
                        //                 borderColor: 'rgba(255, 165, 0, 0.8)',
                        //                 borderWidth: 2,
                        //                 borderDash: [6, 6],
                        //                 label: {
                        //                     enabled: true,
                        //                     content: 'Target: 120.000',
                        //                     backgroundColor: 'rgba(255, 165, 0, 0.8)',
                        //                     position: 'end'
                        //                 }
                        //             }
                        //         }
                        //     }
                        // }
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Gagal memuat data chart:', error);
            }
        });

        $.ajax({
            url: "{{ route('chart_data2') }}",
            method: 'GET',
            success: function(response) {
                let ctx2 = document.getElementById('chart_tren2').getContext('2d');
                let chartInstance2 = new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: response.tanggal,
                        datasets: [{
                                label: 'Pendapatan',
                                data: response.pendapatan,
                                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2
                            },
                            {
                                label: 'Biaya',
                                data: response.biaya,
                                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 2
                            }
                        ]
                    }
                });
            }
        });

        // Chart 3: Laba
        $.ajax({
            url: "{{ route('chart_data3') }}",
            method: 'GET',
            success: function(response) {
                let ctx3 = document.getElementById('chart_tren3').getContext('2d');
                let chartInstance3 = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: response.tanggal,
                        datasets: [{
                            label: 'Laba',
                            data: response.laba,
                            backgroundColor: 'rgba(153, 102, 255, 0.7)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 2
                        }]
                    }
                });
            }
        });
    }

    // Event listener untuk select dropdown
    document.getElementById('chartTypeSelect').addEventListener('change', function() {
        const selectedType = this.value;
        renderChart(selectedType);
    });

    // Render chart awal sebagai bar
    renderChart('bar');

    $.ajax({
        url: "{{route('chart_data_aktivitas')}}", // sesuaikan dengan route kamu
        method: 'GET',
        success: function(response) {
            const ctx2 = document.getElementById('pie_chart').getContext('2d');

            new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: response.labels,
                    datasets: [{
                        label: 'Aktivitas',
                        data: response.data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 235, 133, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 86, 190, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: '#333',
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Gagal ambil data aktivitas:", error);
        }
    });
</script>
<script>
    $(document).ready(function() {
        $('#detailTable').DataTable({
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

<script>
    // Chart 1
    const ctx4 = document.getElementById('chart_tren4').getContext('2d');
    let myChart = new Chart(ctx4, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Pendapatan',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function loadChartData4(year) { // <--- nama function berbeda
        $.ajax({
            url: "{{ url('admin/get_pendapatan2') }}/" + year,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const labelsArray = Object.values(data.labels);
                myChart.data.labels = labelsArray;
                myChart.data.datasets[0].data = data.values;
                myChart.update();
            },
            error: function(xhr, status, error) {
                console.error("Gagal load chart 4:", error);
            }
        });
    }

    $(document).ready(function() {
        const select2 = $("#chartTypeSelect4");
        loadChartData4(select2.val());

        select2.on("change", function() {
            loadChartData4($(this).val());
        });
    });
</script>

<script>
    // Chart 2
    const ctx5 = document.getElementById('chart_tren5').getContext('2d');
    let myChart2 = new Chart(ctx5, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Pendapatan',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function loadChartData5(year) { // <--- nama function berbeda
        $.ajax({
            url: "{{ url('admin/get_pendapatan') }}/" + year,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const labelsArray = Object.values(data.labels);
                myChart2.data.labels = labelsArray;
                myChart2.data.datasets[0].data = data.values;
                myChart2.update();
            },
            error: function(xhr, status, error) {
                console.error("Gagal load chart 5:", error);
            }
        });
    }

    $(document).ready(function() {
        const select = $("#chartTypeSelect5");
        loadChartData5(select.val());

        select.on("change", function() {
            loadChartData5($(this).val());
        });
    });
</script>


<script>
    // Chart 6 (getProduksi)
    const ctx6 = document.getElementById('chart_tren6').getContext('2d');
    const myChart3 = new Chart(ctx6, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Produksi',
                data: [],
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function loadProduksiChart(year) {
        $.ajax({
            url: "{{ url('admin/get_produksi') }}/" + year,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const labelsArray = Object.values(data.labels);
                myChart3.data.labels = labelsArray;
                myChart3.data.datasets[0].data = data.values;
                myChart3.update();
            },
            error: function(xhr, status, error) {
                console.error("Gagal load chart Produksi:", error);
            }
        });
    }

    $(document).ready(function() {
        const select6 = $("#chartTypeSelect6");
        loadProduksiChart(select6.val());
        select6.on("change", function() {
            loadProduksiChart($(this).val());
        });
    });
</script>

<script>
    // Chart 7 (getProduksi2)
    const ctx7 = document.getElementById('chart_tren7').getContext('2d');
    const myChart4 = new Chart(ctx7, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Produksi',
                data: [],
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function loadProduksiChart2(year) {
        $.ajax({
            url: "{{ url('admin/get_produksi2') }}/" + year,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const labelsArray = Object.values(data.labels);
                myChart4.data.labels = labelsArray;
                myChart4.data.datasets[0].data = data.values;
                myChart4.update();
            },
            error: function(xhr, status, error) {
                console.error("Gagal load chart Produksi2:", error);
            }
        });
    }

    $(document).ready(function() {
        const select7 = $("#chartTypeSelect7");
        loadProduksiChart2(select7.val());
        select7.on("change", function() {
            loadProduksiChart2($(this).val());
        });
    });
</script>

@endsection