<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://example.com/background.jpg');
            background-size: cover; /* Agar gambar menutupi seluruh layar */
            background-position: center; /* Posisikan gambar di tengah */
            background-repeat: no-repeat; /* Hindari gambar berulang */
        }
    </style>
</head>
<body class="container mt-5">
    <h1 class="mb-4">Daftar Presensi Siswa</h1>
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

    <div class="modal fade" id="presenceModal" tabindex="-1" aria-labelledby="presenceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8PHLObjAV3hG5ZU7QfJZi0dNa27raOWpegQ&s">
                    <h5 class="modal-title" id="presenceModalLabel">Riwayat Kehadiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h2 id="modalMessage"></h2>
                    <h3 id="modalStudentName"></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Polling AJAX & Modal Logic -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let queue = []; // Antrian data presensi
        let isModalShowing = false; // Status apakah modal sedang tampil

        function checkLatestPresence() {
            $.ajax({
                url: "/api/v1/daily-attendance",
                method: "GET",
                dataType: "json",
                success: function(response) {
                    console.log("Response dari server:", response); // Debugging

                    if (response.new_presence) {
                        queue.push(response); // Tambahkan ke antrian
                        processQueue(); // Proses antrian jika modal tidak sedang tampil
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }

        function processQueue() {
            if (isModalShowing || queue.length === 0) {
                return; // Jika modal sedang tampil atau antrian kosong, jangan lanjut
            }

            let data = queue.shift(); // Ambil data pertama dari antrian
            isModalShowing = true; // Tandai bahwa modal sedang ditampilkan

            console.log("Menampilkan modal untuk:", data.student); // Debugging

            // Tampilkan modal dengan informasi siswa
            $("#modalStudentName").text(data.student);
            $("#modalMessage").text(data.message);
            $("#presenceModal").modal("show");

            // Sembunyikan modal setelah 3 detik
            setTimeout(function() {
                console.log("Menutup modal...");
                $("#presenceModal").modal("hide");
                isModalShowing = false; // Modal sudah ditutup

                console.log("Modal ditutup, cek antrian selanjutnya..."); // Debugging
                processQueue(); // Cek apakah ada data lagi di antrian
            }, 3000);
        }

        // Polling setiap 1 detik
        setInterval(checkLatestPresence, 1000);

        // Cek apakah daftar presensi perlu di-refresh setelah semua modal selesai
        setInterval(function() {
            if (queue.length === 0 && !isModalShowing) {
                console.log("Merefresh daftar presensi..."); // Debugging
                location.reload();
            }
        }, 5000); // Cek setiap 5 detik
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
