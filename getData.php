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

if (isset($_GET['start']) && isset($_GET['end'])) {
    $start_time = $_GET['start'];
    $end_time = $_GET['end'];

    $history_url = "$base_url:$port/api/history/period/" . urlencode($start_time) . "?end_time=" . urlencode($end_time) . "&filter_entity_id=$sensor_entity&minimal_response";
    $history_data = fetchData($history_url, $token);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historical Data</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Historical Data for Sensor</h1>
        <div class="history-data">
            <h2>Sensor Data History</h2>
            <?php
            if (isset($history_data) && !empty($history_data[0])) {
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
        <a href="index.php" class="button">Back to Home</a>
    </div>
</body>
</html>
