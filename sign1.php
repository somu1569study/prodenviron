<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <style>
        /* Your CSS styles */
    </style>
</head>
<body>
<?php
$nameErr = $passErr = $phoneErr = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['name'])) {
        $nameErr = "This field is required";
    } else {
        $pattern = "/^[a-zA-Z]+$/";
        $check = preg_match_all($pattern, $_POST['name']);
        if ($check) {
            $name = $_POST['name'];
        } else {
            $nameErr = "Enter correct pattern of your name";
        }
    }

    if (empty($_POST['password'])) {
        $passErr = "This field is required";
    } else {
        $password = $_POST['password']; // Corrected variable name
        if (strlen($password) < 4) {
            $passErr = "Password must be at least 4 characters long";
        }
    }

    // If there are no errors, insert data into database
    if (empty($nameErr) && empty($passErr)) {
        // Connect to database (replace with your database credentials)
        $conn = new mysqli("localhost", "root", "", "users");

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and bind statement
        $stmt = $conn->prepare("INSERT INTO users1 (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $password);

        // Execute statement
        if ($stmt->execute() === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement and connection
        $stmt->close();
        $conn->close();
    }
}
?>
<div class="container">
    <form id="signupForm" method="post" action="">
        <h2>Signup</h2>
        <input type="text" name="name" id="signupUsername" placeholder="Username" required><br>
        <span class='red-message'>* <?php echo $nameErr; ?></span><br>
        <input type="password" name="password" id="signupPassword" placeholder="Password" required><br>
        <span class='red-message'>* <?php echo $passErr; ?></span><br>
        <button type="submit">Signup</button><br>
    </form>
</div>

</body>
</html>
