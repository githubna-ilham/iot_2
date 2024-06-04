<?php

include 'variables.php';

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

$history_url = "$base_url:$port/api/history/period/" . urlencode(date('c', strtotime('-1 minute'))) . "?filter_entity_id=$sensor_entity&minimal_response";
$history_data = fetchData($history_url, $token);

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

        // Function to refresh the page every 60 seconds
        setInterval(function() {
            window.location.reload();
        }, waktuRefreshSensor);

        // Function to refresh the page 1 second after the switch is toggled
        if (autoRefresh) {
            setTimeout(function() {
                window.location.href = "index.php";
            }, 1000);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Home Assistant Control Panel</h1>
        <div class="switch-status">
            <h2>Switch Control</h2>
            <div>
                <span>Status Lampu: <?php echo ($switch_lampu_data && $switch_lampu_data['state'] === 'on') ? 'Menyala' : 'Mati'; ?></span>
                <a href="?component=lampu&action=on" class="button">Nyalakan Lampu</a>
                <a href="?component=lampu&action=off" class="button">Matikan Lampu</a>
            </div>
            <div>
                <span>Status Fan: <?php echo ($switch_fan_data && $switch_fan_data['state'] === 'on') ? 'Menyala' : 'Mati'; ?></span>
                <a href="?component=fan&action=on" class="button">Nyalakan Fan</a>
                <a href="?component=fan&action=off" class="button">Matikan Fan</a>
            </div>
        </div>

        <div class="sensor-data">
            <h2>Sensor Data</h2>
            <p>Nutrisi Air: <?php echo $sensor_data ? $sensor_data['state'] . '%' : 'Gagal mengambil data dari sensor.'; ?></p>
        </div>

        <div class="history-data">
            <h2>Sensor Data History (1 Minute)</h2>
            <?php
            if ($history_data && !empty($history_data[0])) {
                echo "<table>";
                echo "<tr><th>Time</th><th>State</th></tr>";
                foreach ($history_data[0] as $entry) {
                    $formatted_time = date('d M Y, h:i:s A', strtotime($entry['last_changed']));
                    echo "<tr>";
                    echo "<td>$formatted_time</td>";
                    echo "<td>" . $entry['state'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No history data available for the selected period.</p>";
            }
            ?>
        </div>

        <h2>Get Historical Data</h2>
        <form id="dateForm" action="getData.php" method="GET">
            <label for="start">Start Time:</label>
            <input type="datetime-local" id="start" name="start" max="<?php echo date('Y-m-d\TH:i:s', strtotime('+1 day')); ?>" required><br><br>
            <label for="end">End Time:</label>
            <input type="datetime-local" id="end" name="end" max="<?php echo date('Y-m-d\TH:i:s', strtotime('+1 day')); ?>" required><br><br>
            <input type="submit" value="Submit">
        </form>

        <?php
        if ($operation_message) {
            echo $operation_message;
        }
        ?>
    </div>
</body>
</html>
