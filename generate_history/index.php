<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Table</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<h2>Sensor Data</h2>

<form id="dateForm" action="getData.php" method="GET">
    <label for="start">Start Time:</label>
    <input type="datetime-local" id="start" name="start" max="<?php echo date('Y-m-d\TH:i:s', strtotime('+1 day')); ?>" required><br><br>

    <label for="end">End Time:</label>
    <input type="datetime-local" id="end" name="end" max="<?php echo date('Y-m-d\TH:i:s', strtotime('+1 day')); ?>" required><br><br>

    <input type="submit" value="Submit">
</form>

<div id="dataTable"></div>

</body>
</html>
