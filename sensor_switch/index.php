<?php
$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJlMDljNzViN2ZlNWM0ZTRmOWNiNTc3NGU1ZDIwN2Y0OCIsImlhdCI6MTcxNjc5MTQ0OCwiZXhwIjoyMDMyMTUxNDQ4fQ.kBGF2xfXLrQldqN4ar-vKeNhwta0dFMZEPbRz5t6Yho";
$base_url = "http://172.16.15.14";
$port = "8123";
$webhook_id = "-oI8kThHl1o4MF4VyfuqfDv2O"; // Webhook ID
$switch_lampu_entity = "switch.asri_hydro_lampu_01_relay";
$switch_fan_entity = "switch.asri_hydro_fan_01_relay";
$sensor_entity = "sensor.hydro01_main_percent_nutrisi_air";
$waktu_refresh_sensor = 10000; // Waktu refresh sensor dalam milidetik (10 detik)

function fetchData($url, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer $token"
    ));
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ? json_decode($response, true) : null;
}

$sensor_url = "$base_url:$port/api/states/$sensor_entity";
$switch_lampu_url = "$base_url:$port/api/states/$switch_lampu_entity";
$switch_fan_url = "$base_url:$port/api/states/$switch_fan_entity";
$sensor_data = fetchData($sensor_url, $token);
$switch_lampu_data = fetchData($switch_lampu_url, $token);
$switch_fan_data = fetchData($switch_fan_url, $token);

$action = '';
$operation_message = '';
if (isset($_GET['action']) && isset($_GET['component'])) {
    $action = $_GET['action'];
    $component = $_GET['component'];
    if ($component == 'lampu') {
        $webhook_url = "$base_url:$port/api/webhook/$webhook_id";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhook_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('action' => $action)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $operation_message = "<p>Switch " . strtoupper($action) . " berhasil. Halaman akan refresh dalam 1 detik...</p>";
        } else {
            $operation_message = "<p>Gagal melakukan switch " . strtoupper($action) . ".</p>";
        }
    } elseif ($component == 'fan') {
        $service_url = "$base_url:$port/api/services/switch/turn_$action";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $service_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('entity_id' => $switch_fan_entity)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $operation_message = "<p>Switch " . strtoupper($action) . " berhasil. Halaman akan refresh dalam 1 detik...</p>";
        } else {
            $operation_message = "<p>Gagal melakukan switch " . strtoupper($action) . ".</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Assistant Integration</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        var autoRefresh = <?php echo ($action) ? 'true' : 'false'; ?>;
        var waktuRefreshSensor = <?php echo $waktu_refresh_sensor; ?>;

        // Function to refresh the page every 10 seconds
        setInterval(function() {
            window.location.reload();
        }, waktuRefreshSensor); // waktuRefreshSensor is set in PHP

        // Function to refresh the page 1 second after the switch is toggled
        if (autoRefresh) {
            setTimeout(function() {
                window.location.href = "index.php";
            }, 1000); // 1000 milliseconds = 1 second
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Home Assistant Sensor Data</h1>
        <div class="sensor-data">
            <?php
            if ($sensor_data) {
                echo "Nutrisi Air: " . $sensor_data['state'] . "%";
            } else {
                echo "Gagal mengambil data dari sensor.";
            }
            ?>
        </div>
        <div class="switch-status">
            <?php
            if ($switch_lampu_data) {
                echo "Status Lampu: " . ($switch_lampu_data['state'] === 'on' ? 'Menyala' : 'Mati');
            } else {
                echo "Gagal mengambil status switch lampu.";
            }
            ?>
        </div>
        <a href="?component=lampu&action=on" class="button">Nyalakan Lampu</a>
        <a href="?component=lampu&action=off" class="button">Matikan Lampu</a>
        <div class="switch-status">
            <?php
            if ($switch_fan_data) {
                echo "Status Fan: " . ($switch_fan_data['state'] === 'on' ? 'Menyala' : 'Mati');
            } else {
                echo "Gagal mengambil status switch fan.";
            }
            ?>
        </div>
        <a href="?component=fan&action=on" class="button">Nyalakan Fan</a>
        <a href="?component=fan&action=off" class="button">Matikan Fan</a>
        <?php
        if ($operation_message) {
            echo $operation_message;
        }
        ?>
    </div>
</body>
</html>
