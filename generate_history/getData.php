<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Table</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<?php

// Periksa apakah permintaan adalah metode GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    // Periksa apakah kedua waktu telah disediakan
    if (isset($_GET['start']) && isset($_GET['end'])) {
        
        // Ambil nilai start dan end dari permintaan GET
        $start_time = $_GET['start'];
        $end_time = $_GET['end'];
        
        // Informasi koneksi dan autentikasi
        $ip_address = "172.16.15.14";
        $port = "8123";
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJlMDljNzViN2ZlNWM0ZTRmOWNiNTc3NGU1ZDIwN2Y0OCIsImlhdCI6MTcxNjc5MTQ0OCwiZXhwIjoyMDMyMTUxNDQ4fQ.kBGF2xfXLrQldqN4ar-vKeNhwta0dFMZEPbRz5t6Yho";
        $entity_id = "sensor.hydro01_main_percent_nutrisi_air";
        
        // Command curl untuk mengambil data dari API Home Assistant
        $curl_command = 'curl -H "Authorization: Bearer ' . $token . '" -H "Content-Type: application/json" "http://' . $ip_address . ':' . $port . '/api/history/period/' . urlencode($start_time) . '?end_time=' . urlencode($end_time) . '&filter_entity_id=' . urlencode($entity_id) . '&minimal_response"';

        // Menjalankan command curl dan menyimpan outputnya ke dalam variabel
        $data_json = shell_exec($curl_command);

        // Mengurai data JSON
        $data = json_decode($data_json, true);

        // Mengecek apakah ada data
        if (!empty($data)) {
            // Menampilkan nama entity_id
            echo "<h2>" . $entity_id . "</h2>";

            // Membuka tabel
            echo "<table>";
            echo "<tr><th>Time</th><th>State</th></tr>";
            
            // Mengambil setiap entri dalam data dan menampilkan sebagai baris tabel
            foreach ($data[0] as $entry) {
                echo "<tr>";
                echo "<td>".$entry['last_changed']."</td>";
                echo "<td>".$entry['state']."</td>";
                echo "</tr>";
            }

            // Menutup tabel
            echo "</table>";

        } else {
            echo "No data available.";
        }
        
    } else {
        // Jika waktu tidak disediakan, beri tahu pengguna
        echo "Please provide both start and end times.";
    }
    
} else {
    // Jika metode permintaan bukan GET, beri tahu pengguna bahwa hanya metode GET yang diizinkan
    echo "Only GET method is allowed.";
}

?>
</body>
</html>
