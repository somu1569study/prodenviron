<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
$id = $_SESSION['id'];
// Check if latitude, longitude, and label are set
if (isset($_POST['latitude'], $_POST['longitude'], $_POST['label'])) {
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $label = $_POST['label'];



// Prepare and bind statement
$stmt = $conn->prepare("INSERT INTO userstore (Uid, lat, lon, label) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $id, $latitude, $longitude, $label);

// Execute statement
if ($stmt->execute() === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
} 

}
else {
    echo "Latitude, longitude, and label must not be empty.";
}
$conn->close();
?>
<html>
<head>

</head>
<body>
<form action="/use.php">
<input type="submit" value="Go Back">
</form>
</body>
</html>
