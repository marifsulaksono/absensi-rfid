<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sistem Manajemen Absensi Digital</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body,
        html {
            height: 100%;
            margin: 0;
        }

        .left-panel {
            background-color: #4DA8DA;
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .right-panel {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .table-container {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .image-header {
            margin: 0;
            padding: 0;
            width: 100%;
            display: block;
            position: relative;
        }

        .image-footer {
            margin: 0;
            padding: 0;
            width: 100%;
            position: absolute;
            bottom: 0;
        }
    </style>
</head>

<body>
    <div class="d-flex h-100 position-relative">
        <!-- KIRI -->
        <div class="left-panel col-md-3 position-relative">
            <img src="{{ asset('images/rfid-logo.png') }}"
                alt="RFID" class="image-header mb-4">
            <h1 class="text-left">Sistem Manajemen Absensi Digital</h1>
            <img src="{{ asset('images/logo-footer.svg') }}" alt="" class="image-footer">
        </div>

        <!-- KANAN -->
        <div class="right-panel col-md-9 bg-light">
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 bg-white p-4 shadow-sm">
                <img src="https://upload.wikimedia.org/wikipedia/commons/e/e6/Logo_Kabupaten_Probolinggo_-_Seal_of_Probolinggo_Regency.svg"
                    alt="Logo Instansi" width="60">
                <div>
                    <h2 class="fw-bold mb-0">SDN Patokan II Kraksaan</h2>
                    <h4 class="fw-bold mb-0">Probolinggo, Jawa Timur</h4>
                </div>
            </div>

            <!-- Tabel Presensi -->
            <div class="table-container" id="absensiTable">
                <table class="table table-striped">
                    <thead>
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th width="35%">Nama</th>
                            <th width="20%">Tanggal</th>
                            <th width="20%">Jam Masuk</th>
                            <th width="20%">Jam Pulang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($absensi as $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data->student->name ?? 'Unknown' }}</td>
                            <td>{{ $data->date }}</td>
                            <td>{{ $data->in }}</td>
                            <td>{{ $data->out }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="presenceModal" tabindex="-1" aria-labelledby="presenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-2 border-primary">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title" id="presenceModalLabel">Data Siswa</h5>
                </div>

                <div class="modal-body p-4 d-flex align-items-center" style="min-height: 200px;">
                    <div class="d-flex w-100">
                        <!-- Foto -->
                        <div class="me-4">
                            <img id="modalStudentPhoto" src="" onerror="this.src='{{ asset('images/photo.jpg') }}'" width="100" height="120">
                        </div>

                        <!-- Informasi Siswa -->
                        <div class="flex-grow-1">
                            <p class="mb-1"><strong>Nama:</strong> <span id="modalStudentName">-</span></p>
                            <p class="mb-1"><strong>Kelas:</strong> <span id="modalStudentClass">-</span></p>
                            <p class="mb-1"><strong>Alamat:</strong> <span id="modalStudentAddress">-</span></p>
                            <p class="mb-1"><strong>NIS:</strong> <span id="modalStudentID">-</span></p>
                            <p class="mb-0 text-primary fw-bold" id="modalMessage">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let queue = [];
        let isModalShowing = false;

        function checkLatestPresence() {
            $.ajax({
                url: "/api/v1/daily-attendance",
                method: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.new_presence) {
                        queue.push(response);
                        processQueue(); // selalu coba proses antrian setelah push
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }

        function processQueue() {
            if (isModalShowing || queue.length === 0) return;

            const data = queue.shift(); // Ambil item pertama dari antrian
            isModalShowing = true;

            $("#modalStudentName").text(data.student_name);
            $("#modalStudentClass").text(data.student_class);
            $("#modalStudentAddress").text(data.student_address);
            $("#modalStudentID").text(data.student_nis);
            $("#modalStudentPhoto").attr("src", data.student_photo);
            $("#modalMessage").text(data.message);
            $("#presenceModal").modal("show");

            // Tentukan durasi modal: 5 detik jika queue kosong (hanya 1 data), 3 detik jika masih ada antrian
            const modalDuration = (queue.length === 0) ? 5000 : 3000;

            setTimeout(function () {
                $("#presenceModal").modal("hide");
                isModalShowing = false;

                // Proses data berikutnya setelah modal ditutup
                processQueue();
            }, modalDuration);
        }

        // Polling setiap 1 detik untuk ambil data presensi terbaru
        function pollPresence() {
            checkLatestPresence();
            setTimeout(pollPresence, 1000); // rekursif agar tidak overlap seperti setInterval
        }
        pollPresence();

        // Refresh tabel setiap 5 detik jika tidak ada modal yang aktif
        setInterval(function () {
            if (!isModalShowing && queue.length === 0) {
                $("#absensiTable").load(location.href + " #absensiTable>*", "");
            }
        }, 5000);
    </script>

</body>

</html>


<?php
    // <!DOCTYPE html>
    // <html lang="en">
    
    // <head>
    //     <meta charset="UTF-8">
    //     <title>Sistem Manajemen Absensi Digital</title>
    //     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    //     <style>
    //         body,
    //         html {
    //             height: 100%;
    //             margin: 0;
    //         }
    
    //         .left-panel {
    //             background-color: #0d6efd;
    //             color: white;
    //             padding: 2rem;
    //             display: flex;
    //             flex-direction: column;
    //             align-items: center;
    //         }
    
    //         .right-panel {
    //             display: flex;
    //             flex-direction: column;
    //             height: 100vh;
    //             overflow: hidden;
    //         }
    
    //         .table-container {
    //             flex-grow: 1;
    //             overflow-y: auto;
    //             padding: 1rem;
    //         }
    
    //         .image-header {
    //             margin-top: -2rem
    //         }
    
    //         .image-footer {
    //             margin-top: 9rem
    //         }
            
    
    //     </style>
    // </head>
    
    // <body>
    //     <div class="d-flex h-100">
    //         <!-- KIRI -->
    //         <div class="left-panel col-md-3">
    //             <img src="https://e7.pngegg.com/pngimages/183/248/png-clipart-access-control-radio-frequency-identification-television-pensiunea-mioval-technology-rfid-card-logo-transmitter.png"
    //                 alt="RFID" class="image-header mb-4" width="400">
    //             <h1 class="text-left">Sistem Manajemen Absensi Digital</h1>
    //                 <img src="{{ asset('images/logo-footer.svg') }}" alt="" width="130%" height="130%" class="image-footer">
    //         </div>
    
    //         <!-- KANAN -->
    //         <div class="right-panel col-md-9 bg-light">
    //             <!-- Header -->
    //             <div class="d-flex align-items-center gap-3 bg-white p-4 shadow-sm">
    //                 <img src="https://upload.wikimedia.org/wikipedia/commons/e/e6/Logo_Kabupaten_Probolinggo_-_Seal_of_Probolinggo_Regency.svg"
    //                     alt="Logo Instansi" width="60">
    //                 <div>
    //                     <h2 class="fw-bold mb-0">SDN Patokan II Kraksaan</h2>
    //                     <h4 class="fw-bold mb-0">Probolinggo, Jawa Timur</h4>
    //                 </div>
    //             </div>
    
    //             <!-- Tabel Presensi -->
    //             <div class="table-container" id="absensiTable">
    //                 <table class="table table-striped">
    //                     <thead>
    //                         <tr class="text-center">
    //                             <th width="5%">No</th>
    //                             <th width="35%">Nama</th>
    //                             <th width="20%">Tanggal</th>
    //                             <th width="20%">Jam Masuk</th>
    //                             <th width="20%">Jam Pulang</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>
    //                         @foreach ($absensi as $data)
    //                         <tr>
    //                             <td>{{ $loop->iteration }}</td>
    //                             <td>{{ $data->student->name ?? 'Unknown' }}</td>
    //                             <td>{{ $data->date }}</td>
    //                             <td>{{ $data->in }}</td>
    //                             <td>{{ $data->out }}</td>
    //                         </tr>
    //                         @endforeach
    //                     </tbody>
    //                 </table>
    //             </div>
    //         </div>
    //     </div>
    
    //     <!-- Modal -->
    //     <div class="modal fade" id="presenceModal" tabindex="-1" aria-labelledby="presenceModalLabel" aria-hidden="true">
    //         <div class="modal-dialog">
    //             <div class="modal-content">
    //                 <div class="modal-header">
    //                     <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8PHLObjAV3hG5ZU7QfJZi0dNa27raOWpegQ&s"
    //                         width="40">
    //                     <h5 class="modal-title ms-2" id="presenceModalLabel">Riwayat Kehadiran</h5>
    //                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    //                 </div>
    //                 <div class="modal-body">
    //                     <h2 id="modalMessage"></h2>
    //                     <h3 id="modalStudentName"></h3>
    //                 </div>
    //             </div>
    //         </div>
    //     </div>
        
    
    //     <!-- Scripts -->
    //     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    //     <script>
    //         let queue = [];
    //         let isModalShowing = false;
    
    //         function checkLatestPresence() {
    //             $.ajax({
    //                 url: "/api/v1/daily-attendance",
    //                 method: "GET",
    //                 dataType: "json",
    //                 success: function (response) {
    //                     if (response.new_presence) {
    //                         queue.push(response);
    //                         processQueue();
    //                     }
    //                 },
    //                 error: function (xhr, status, error) {
    //                     console.error("AJAX Error:", error);
    //                 }
    //             });
    //         }
    
    //         function processQueue() {
    //             if (isModalShowing || queue.length === 0) return;
    
    //             let data = queue.shift();
    //             isModalShowing = true;
    
    //             $("#modalStudentName").text(data.student);
    //             $("#modalMessage").text(data.message);
    //             $("#presenceModal").modal("show");
    
    //             setTimeout(function () {
    //                 $("#presenceModal").modal("hide");
    //                 isModalShowing = false;
    //                 processQueue();
    //             }, 3000);
    //         }
    
    //         setInterval(checkLatestPresence, 1000);
    
    //         setInterval(function () {
    //             if (queue.length === 0 && !isModalShowing) {
    //                 $("#absensiTable").load(location.href + " #absensiTable>*", "");
    //             }
    //         }, 5000);
    
    //     </script>
    
    //     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    // </body>
    
    // </html>
    
?>